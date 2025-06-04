<?php

use App\Models\User;
use App\Models\SearchHistory;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => '1', 'direction' => 'asc'];
    public array $searchResults = [];
    public bool $isLoading = false;
    public bool $hasSearched = false;
    public int $searchProgress = 0;
    private static bool $searchLock = false;
    
    // Debug properties
    public bool $showDebug = false;
    public array $debugInfo = [];

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->debugInfo = [];
        $this->success('Filters cleared.', position: 'toast-bottom');
    }
    
    // Toggle debug panel
    public function toggleDebug(): void
    {
        $this->showDebug = !$this->showDebug;
    }
    
    // Add debug message
    private function addDebug(string $type, string $message, $data = null): void
    {
        $this->debugInfo[] = [
            'timestamp' => now()->format('H:i:s.u'),
            'type' => $type,
            'message' => $message,
            'data' => $data
        ];
        
        // Keep only the last 50 debug messages
        if (count($this->debugInfo) > 50) {
            array_shift($this->debugInfo);
        }
        
        // Also log to Laravel log
        \Log::debug("[PARTS-SEARCH] [$type] $message", ['data' => $data]);
    }
    
    // Cancel a search in progress
    public function cancelSearch(): void
    {
        // Get the search information from the session
        $pid = session('search_pid');
        $outputFile = session('search_output_file');
        
        // If we have a process ID, try to kill it
        if ($pid) {
            shell_exec('kill -9 ' . intval($pid) . ' 2>/dev/null');
        }
        
        // If we have an output file, delete it
        if ($outputFile && file_exists($outputFile)) {
            @unlink($outputFile);
        }
        
        // Clean up session data
        session()->forget(['search_id', 'search_pid', 'search_output_file', 'search_term', 'search_started']);
        
        // Release the search lock and reset progress
        self::$searchLock = false;
        $this->isLoading = false;
        $this->searchProgress = 0;
        
        $this->info('Search cancelled.', position: 'toast-bottom');
    }


    // Start the search process
    public function performSearch(): void
    {
        // Check if a search is already running
        if (self::$searchLock) {
            $this->warning('A search is already in progress. Please wait.', position: 'toast-bottom');
            return;
        }
        
        if (empty($this->search)) {
            $this->error('Please enter a part number to search', position: 'toast-bottom');
            return;
        }
        
        // Set the search lock
        self::$searchLock = true;
        
        // Set loading state and reset progress
        $this->isLoading = true;
        $this->searchProgress = 0;
        $this->info('Starting search... This may take up to 2 minutes.', position: 'toast-bottom');
        
        // Get the search term
        $searchTerm = $this->search;
        
        // Create initial search history entry
        $searchHistory = SearchHistory::create([
            'user_id' => auth()->id(),
            'team_id' => auth()->user()->current_team_id,
            'search_term' => $searchTerm,
            'results_count' => 0,
            'success' => false,
        ]);
        
        // Create a unique ID for this search
        $searchId = uniqid('search_');
        $outputFile = storage_path('app/playwright_' . $searchId . '.json');
        
        // Run the Playwright test in the background
        $playwrightPath = base_path('playwright');
        $escapedSearchTerm = escapeshellarg($searchTerm);
        $escapedOutputFile = escapeshellarg($outputFile);

        $tmpOutputFile = $outputFile . '.tmp';
        
        $playwrightCli = './node_modules/.bin/playwright'; // Replace with output of `which playwright`
        $command = 'nohup bash -c ' . escapeshellarg(
            'cd ' . $playwrightPath . ' && ' .
            'SEARCH_TERM=' . escapeshellarg($searchTerm) . ' ' .
            $playwrightCli . ' test tests/partsbase.spec.ts --project=chromium --reporter=list ' .
            '> ' . escapeshellarg($tmpOutputFile) . ' 2>&1 && ' .
            'mv ' . escapeshellarg($tmpOutputFile) . ' ' . escapeshellarg($outputFile)
        ) . ' > /dev/null 2>&1 & echo $!';
        
        // Debug info
        $this->addDebug('COMMAND', 'Executing playwright command', [
            'search_term' => $searchTerm,
            'output_file' => $outputFile,
            'tmp_output_file' => $tmpOutputFile,
            'playwright_path' => $playwrightPath,
            'command' => $command
        ]);
        
        // Execute the command and get the process ID
        $pid = shell_exec($command);
        
        // Store the search information in the session
        session([
            'search_id' => $searchId,
            'search_pid' => trim($pid),
            'search_output_file' => $outputFile,
            'search_term' => $searchTerm,
            'search_started' => time(),
            'search_history_id' => $searchHistory->id
        ]);
        
        // Debug info
        $this->addDebug('PROCESS', 'Started Playwright search process', [
            'search_id' => $searchId,
            'pid' => trim($pid),
            'output_file' => $outputFile
        ]);
    }
    
    // Check the status of the search
    public function checkSearchStatus(): void
    {
        // If we're not in a loading state, do nothing
        if (!$this->isLoading) {
            return;
        }
        
        // Get the search information from the session
        $searchId = session('search_id');
        $outputFile = session('search_output_file');
        $searchStarted = session('search_started', 0);
        $pid = session('search_pid');
        $searchHistoryId = session('search_history_id');
        
        // If we don't have a search ID, something went wrong
        if (empty($searchId) || empty($outputFile)) {
            $this->addDebug('ERROR', 'Search information not found in session');
            $this->error('Search information not found.', position: 'toast-bottom');
            $this->isLoading = false;
            self::$searchLock = false;
            return;
        }
        
        // Debug current status
        $this->addDebug('STATUS_CHECK', 'Checking search status', [
            'search_id' => $searchId,
            'pid' => $pid,
            'elapsed_time' => time() - $searchStarted,
            'output_file' => $outputFile,
            'file_exists' => file_exists($outputFile)
        ]);
        
        // Check if the output file exists
        if (!file_exists($outputFile)) {
            // Calculate how long the search has been running
            $elapsedTime = time() - $searchStarted;
            
            // Update the progress (max 100%)
            $this->searchProgress = min(95, ($elapsedTime / 120) * 100);
            
            // Check if process is still running
            $processRunning = false;
            if ($pid) {
                $checkCommand = 'ps -p ' . intval($pid) . ' > /dev/null 2>&1; echo $?';
                $processRunning = trim(shell_exec($checkCommand)) === '0';
            }
            
            $this->addDebug('WAITING', 'Output file not found yet', [
                'elapsed_seconds' => $elapsedTime,
                'progress' => $this->searchProgress,
                'process_running' => $processRunning
            ]);
            
            // If the search has been running for more than 5 minutes, assume it failed
            if ($elapsedTime > 300) {
                $this->addDebug('TIMEOUT', 'Search timed out after 5 minutes');
                $this->error('Search timed out after 5 minutes.', position: 'toast-bottom');
                $this->isLoading = false;
                self::$searchLock = false;
                $this->hasSearched = true;
                session()->forget(['search_id', 'search_pid', 'search_output_file', 'search_term', 'search_started']);
            }
            return;
        }
        
        // Read the output file
        $output = file_get_contents($outputFile);
        $fileSize = filesize($outputFile);
        
        // Debug raw output
        $this->addDebug('OUTPUT_FILE', 'Read output file', [
            'file_size' => $fileSize,
            'output_length' => strlen($output),
            'output_preview' => substr($output, 0, 500),
            'output_full' => $output
        ]);
        
        // Parse the JSON output
        $json = null;
        $jsonStr = null;
        if ($output) {
            // Look for JSON in the output
            if (preg_match('/(\{.*\})/s', $output, $matches)) {
                $jsonStr = $matches[1];
                $json = json_decode($jsonStr, true);
                
                $this->addDebug('JSON_PARSE', 'Extracted and parsed JSON', [
                    'json_found' => true,
                    'json_valid' => $json !== null,
                    'json_error' => json_last_error_msg(),
                    'json_string' => $jsonStr,
                    'parsed_data' => $json
                ]);
            } else {
                $this->addDebug('JSON_PARSE', 'No JSON found in output', [
                    'json_found' => false
                ]);
            }
        }
        
        // Set progress to 100% when we have a result
        $this->searchProgress = 100;
        
        if ($json && isset($json['success']) && $json['success'] === true) {
            $this->searchResults = $json['data'] ?? [];
            $this->addDebug('SUCCESS', 'Search completed successfully', [
                'result_count' => count($this->searchResults),
                'results' => $this->searchResults
            ]);
            
            // Update search history with success
            if ($searchHistoryId) {
                SearchHistory::where('id', $searchHistoryId)->update([
                    'results_count' => count($this->searchResults),
                    'success' => true,
                    'search_results' => count($this->searchResults) <= 50 ? $this->searchResults : null, // Only store if not too many results
                ]);
            }
            
            $this->success('Search completed successfully.', position: 'toast-bottom');
        } else {
            $this->searchResults = [];
            $errorMessage = 'Unknown error';
            if (!$output) {
                $errorMessage = 'No output from Playwright test';
            } elseif (!$json) {
                $errorMessage = 'Failed to parse JSON from output';
            } elseif (isset($json['success']) && $json['success'] === false) {
                $errorMessage = $json['error'] ?? 'Search failed';
            }
            
            $this->addDebug('ERROR', 'Search failed', [
                'error_message' => $errorMessage,
                'json_data' => $json
            ]);
            
            $this->error('Failed to get data: ' . $errorMessage, position: 'toast-bottom');
            
            // Update search history with failure
            if ($searchHistoryId) {
                SearchHistory::where('id', $searchHistoryId)->update([
                    'success' => false,
                ]);
            }
        }
        
        // Clean up
        @unlink($outputFile);
        session()->forget(['search_id', 'search_pid', 'search_output_file', 'search_term', 'search_started', 'search_history_id']);
        
        // Release the search lock
        self::$searchLock = false;
        $this->isLoading = false;
        $this->hasSearched = true;
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => '1', 'label' => 'Part Number', 'class' => 'w-32'],
            ['key' => '2', 'label' => 'Manufacturer', 'class' => 'w-48'],
            ['key' => '3', 'label' => 'Description', 'class' => 'w-48'],
            ['key' => '4', 'label' => 'Condition', 'class' => 'w-24'],
            ['key' => '5', 'label' => 'Type', 'class' => 'w-24'],
            ['key' => '6', 'label' => 'Quantity', 'class' => 'w-24'],
            ['key' => '7', 'label' => 'Location', 'class' => 'w-32'],
        ];
    }

    // Check if a search is in progress
    public function isSearchInProgress(): bool
    {
        return self::$searchLock;
    }
    
    // Clean up when the component is dehydrated (e.g., when the user navigates away)
    public function dehydrate(): void
    {
        // If we have a search in progress, clean up
        $outputFile = session('search_output_file');
        if ($outputFile && file_exists($outputFile)) {
            @unlink($outputFile);
        }
        
        // Release the search lock
        self::$searchLock = false;
    }
    
    public function with(): array
    {
        if (!$this->hasSearched) {
            return [
                'users' => collect([]),
                'headers' => $this->headers(),
                'isLoading' => $this->isLoading,
                'hasSearched' => $this->hasSearched,
                'searchLocked' => $this->isSearchInProgress()
            ];
        }
        
        // Process search results into a format compatible with the table
        $processedResults = collect($this->searchResults)
            ->filter(fn($row) => count($row) > 5) // Filter out empty or header rows
            ->map(function($row) {
                // Map the array data to a keyed array for the table
                return [
                    'id' => $row[0] ?? '',
                    '1' => $row[1] ?? '', // Part Number
                    '2' => $row[3] ?? '', // Manufacturer
                    '3' => $row[4] ?? '', // Description
                    '4' => $row[5] ?? '', // Condition
                    '5' => $row[6] ?? '', // Type
                    '6' => $row[7] ?? '', // Quantity
                    '7' => $row[11] ?? '', // Location
                ];
            });
            
        return [
            'users' => $processedResults,
            'headers' => $this->headers(),
            'isLoading' => $this->isLoading,
            'hasSearched' => $this->hasSearched,
            'searchLocked' => $this->isSearchInProgress()
        ];
    }
}; ?>

<div wire:poll.5s="checkSearchStatus">
    <!-- HEADER -->
    <x-header title="Parts Base Product Search" separator :progress-indicator="$isLoading">
        <x-slot:middle class="!justify-end flex gap-2">
            <form wire:submit="performSearch" class="flex gap-2 w-full max-w-md">
                <x-input 
                    placeholder="Enter part ID..." 
                    wire:model="search" 
                    clearable 
                    icon="o-magnifying-glass" 
                    class="w-full"
                    :disabled="$isLoading"
                />
                <x-button type="submit" label="Search" icon="o-magnifying-glass" class="btn-primary" :disabled="$isLoading" spinner />
            </form>
        </x-slot:middle>
    </x-header>

    <!-- DEBUG PANEL -->
    @if($showDebug)
    <x-card class="mb-4 bg-base-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Debug Information</h3>
            <x-button wire:click="toggleDebug" icon="o-x-mark" class="btn-sm btn-ghost" />
        </div>
        <div class="overflow-auto max-h-96 text-xs font-mono">
            @foreach(array_reverse($debugInfo) as $debug)
            <div class="mb-2 p-2 bg-base-100 rounded">
                <div class="flex gap-2 items-center mb-1">
                    <span class="text-gray-500">{{ $debug['timestamp'] }}</span>
                    <span class="badge badge-sm 
                        @if($debug['type'] === 'ERROR') badge-error
                        @elseif($debug['type'] === 'SUCCESS') badge-success
                        @elseif($debug['type'] === 'COMMAND') badge-primary
                        @else badge-info
                        @endif">
                        {{ $debug['type'] }}
                    </span>
                    <span class="font-semibold">{{ $debug['message'] }}</span>
                </div>
                @if($debug['data'])
                <pre class="text-xs overflow-auto bg-base-200 p-1 rounded">{{ json_encode($debug['data'], JSON_PRETTY_PRINT) }}</pre>
                @endif
            </div>
            @endforeach
        </div>
    </x-card>
    @endif

    <!-- TABLE  -->
    <x-card shadow>
        <!-- Debug button in top right corner -->
        <div class="absolute top-4 right-4">
            <x-button wire:click="toggleDebug" icon="o-bug-ant" class="btn-sm btn-ghost" title="Toggle Debug Info" />
        </div>
        @if($isLoading)
            <div class="flex flex-col justify-center items-center p-8 gap-4">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="text-gray-500">Searching PartsBase... This may take up to 2 minutes.</p>
                
                <!-- Progress bar -->
                <div class="w-full max-w-md mt-4">
                    <div class="bg-gray-200 rounded-full h-2.5">
                        <div class="bg-primary h-2.5 rounded-full" style="width: {{ $searchProgress }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 text-center">{{ round($searchProgress) }}% complete</p>
                </div>
                
                <x-button wire:click="cancelSearch" class="btn-error mt-4">Cancel Search</x-button>
            </div>
        @elseif(!$hasSearched)
            <div class="text-center py-12">
                <x-icon name="o-document-magnifying-glass" class="w-24 h-24 mx-auto text-base-content/20" />
                <h3 class="text-xl font-semibold mt-6">Search for Parts</h3>
                <p class="text-base-content/70 mt-2 max-w-md mx-auto">
                    Enter a part ID in the search box above to find parts. You'll be able to see a list of your searches in your teams search history.
                </p>
            </div>
        @elseif(count($users) === 0)
            <div class="p-8 text-center text-gray-500">
                No results found for "{{ $search }}"
            </div>
        @else
            <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" />
        @endif
    </x-card>
</div>
