<?php

namespace App\Livewire\Parts;

use App\Models\SearchHistory;
use App\Services\OpenAIService;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Log;

class ChatSearch extends Component
{
    use Toast;

    public array $messages = [];
    public string $userInput = '';
    public bool $isSearching = false;
    public int $searchProgress = 0;
    private static bool $searchLock = false;
    
    // Search results
    public array $searchResults = [];
    public string $currentSearchTerm = '';
    
    // Chat state
    public bool $isTyping = false;
    
    protected OpenAIService $openAIService;

    public function boot(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function mount()
    {
        // Add initial greeting message
        $this->messages[] = [
            'role' => 'assistant',
            'content' => 'Hello! I\'m your aircraft parts search assistant. You can ask me to search for parts by typing something like "Find part ABC123" or "I need pricing for XYZ789". How can I help you today?',
            'timestamp' => now()
        ];
    }

    public function sendMessage()
    {
        if (empty(trim($this->userInput))) {
            return;
        }

        // Add user message to chat
        $userMessage = trim($this->userInput);
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()
        ];

        // Clear input
        $this->userInput = '';
        
        // Show typing indicator
        $this->isTyping = true;

        // Parse the user's message to extract part information
        $parsedQuery = $this->openAIService->parsePartQuery($userMessage);

        if ($parsedQuery['success'] && isset($parsedQuery['part_number'])) {
            // We found a part number, initiate search
            $partNumber = $parsedQuery['part_number'];
            $this->currentSearchTerm = $partNumber;
            
            // Add assistant response
            $this->messages[] = [
                'role' => 'assistant',
                'content' => "I'll search for part number **{$partNumber}** on PartsBase. This may take up to 2 minutes...",
                'timestamp' => now()
            ];
            
            $this->isTyping = false;
            
            // Start the Playwright search
            $this->performSearch($partNumber);
        } else {
            // Couldn't extract a part number
            $this->isTyping = false;
            
            $response = $parsedQuery['message'] ?? 'I couldn\'t identify a specific part number in your request. Please provide a part number like "ABC123" or "XYZ789" so I can search for it.';
            
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $response,
                'timestamp' => now()
            ];
        }
    }

    private function performSearch(string $searchTerm): void
    {
        // Check if a search is already running
        if (self::$searchLock) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'A search is already in progress. Please wait for it to complete.',
                'timestamp' => now()
            ];
            return;
        }
        
        // Set the search lock
        self::$searchLock = true;
        
        // Set loading state and reset progress
        $this->isSearching = true;
        $this->searchProgress = 0;
        
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
        $tmpOutputFile = $outputFile . '.tmp';
        
        $playwrightCli = './node_modules/.bin/playwright';
        $command = 'nohup bash -c ' . escapeshellarg(
            'cd ' . $playwrightPath . ' && ' .
            'SEARCH_TERM=' . escapeshellarg($searchTerm) . ' ' .
            $playwrightCli . ' test tests/partsbase.spec.ts --project=chromium --reporter=list ' .
            '> ' . escapeshellarg($tmpOutputFile) . ' 2>&1 && ' .
            'mv ' . escapeshellarg($tmpOutputFile) . ' ' . escapeshellarg($outputFile)
        ) . ' > /dev/null 2>&1 & echo $!';
        
        // Execute the command and get the process ID
        $pid = shell_exec($command);
        
        // Store the search information in the session
        session([
            'chat_search_id' => $searchId,
            'chat_search_pid' => trim($pid),
            'chat_search_output_file' => $outputFile,
            'chat_search_term' => $searchTerm,
            'chat_search_started' => time(),
            'chat_search_history_id' => $searchHistory->id
        ]);
        
        Log::debug("[CHAT-SEARCH] Started Playwright search", [
            'search_id' => $searchId,
            'pid' => trim($pid),
            'search_term' => $searchTerm
        ]);
    }

    public function checkSearchStatus(): void
    {
        // If we're not searching, do nothing
        if (!$this->isSearching) {
            return;
        }
        
        // Get the search information from the session
        $searchId = session('chat_search_id');
        $outputFile = session('chat_search_output_file');
        $searchStarted = session('chat_search_started', 0);
        $pid = session('chat_search_pid');
        $searchHistoryId = session('chat_search_history_id');
        
        // If we don't have a search ID, something went wrong
        if (empty($searchId) || empty($outputFile)) {
            $this->isSearching = false;
            self::$searchLock = false;
            return;
        }
        
        // Check if the output file exists
        if (!file_exists($outputFile)) {
            // Calculate how long the search has been running
            $elapsedTime = time() - $searchStarted;
            
            // Update the progress (max 95%)
            $this->searchProgress = min(95, ($elapsedTime / 120) * 100);
            
            // If the search has been running for more than 5 minutes, assume it failed
            if ($elapsedTime > 300) {
                $this->handleSearchFailure('Search timed out after 5 minutes.');
            }
            return;
        }
        
        // Read the output file
        $output = file_get_contents($outputFile);
        
        // Parse the JSON output
        $json = null;
        if ($output && preg_match('/(\{.*\})/s', $output, $matches)) {
            $json = json_decode($matches[1], true);
        }
        
        // Set progress to 100%
        $this->searchProgress = 100;
        
        if ($json && isset($json['success']) && $json['success'] === true) {
            $this->searchResults = $json['data'] ?? [];
            
            // Update search history with success
            if ($searchHistoryId) {
                SearchHistory::where('id', $searchHistoryId)->update([
                    'results_count' => count($this->searchResults),
                    'success' => true,
                    'search_results' => count($this->searchResults) <= 50 ? $this->searchResults : null,
                ]);
            }
            
            // Display results in chat
            $this->displaySearchResults();
        } else {
            $errorMessage = 'Failed to retrieve search results.';
            if ($json && isset($json['error'])) {
                $errorMessage = $json['error'];
            }
            $this->handleSearchFailure($errorMessage);
        }
        
        // Clean up
        @unlink($outputFile);
        session()->forget(['chat_search_id', 'chat_search_pid', 'chat_search_output_file', 'chat_search_term', 'chat_search_started', 'chat_search_history_id']);
        
        // Release the search lock
        self::$searchLock = false;
        $this->isSearching = false;
    }

    private function displaySearchResults(): void
    {
        if (empty($this->searchResults)) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => "I couldn't find any results for part number **{$this->currentSearchTerm}**. The part might not be available or the part number might be incorrect.",
                'timestamp' => now()
            ];
            return;
        }

        $resultCount = count($this->searchResults);
        $message = "I found **{$resultCount} result" . ($resultCount > 1 ? 's' : '') . "** for part number **{$this->currentSearchTerm}**:\n\n";
        
        // Format top 5 results
        $displayCount = min(5, $resultCount);
        for ($i = 0; $i < $displayCount; $i++) {
            $row = $this->searchResults[$i];
            if (count($row) > 11) {
                $partNumber = $row[1] ?? 'N/A';
                $manufacturer = $row[3] ?? 'N/A';
                $description = $row[4] ?? 'N/A';
                $condition = $row[5] ?? 'N/A';
                $quantity = $row[7] ?? 'N/A';
                $location = $row[11] ?? 'N/A';
                
                $message .= "**{$partNumber}**\n";
                $message .= "- Manufacturer: {$manufacturer}\n";
                $message .= "- Description: {$description}\n";
                $message .= "- Condition: {$condition}\n";
                $message .= "- Quantity: {$quantity}\n";
                $message .= "- Location: {$location}\n\n";
            }
        }
        
        if ($resultCount > 5) {
            $message .= "_...and " . ($resultCount - 5) . " more results._\n\n";
        }
        
        $message .= "Would you like to search for another part or get more details about these results?";
        
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $message,
            'timestamp' => now(),
            'has_results' => true
        ];
    }

    private function handleSearchFailure(string $errorMessage): void
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => "I encountered an error while searching: {$errorMessage}\n\nWould you like me to try searching again?",
            'timestamp' => now()
        ];
        
        // Update search history with failure
        $searchHistoryId = session('chat_search_history_id');
        if ($searchHistoryId) {
            SearchHistory::where('id', $searchHistoryId)->update([
                'success' => false,
            ]);
        }
    }

    public function cancelSearch(): void
    {
        // Get the search information from the session
        $pid = session('chat_search_pid');
        $outputFile = session('chat_search_output_file');
        
        // If we have a process ID, try to kill it
        if ($pid) {
            shell_exec('kill -9 ' . intval($pid) . ' 2>/dev/null');
        }
        
        // If we have an output file, delete it
        if ($outputFile && file_exists($outputFile)) {
            @unlink($outputFile);
        }
        
        // Clean up session data
        session()->forget(['chat_search_id', 'chat_search_pid', 'chat_search_output_file', 'chat_search_term', 'chat_search_started']);
        
        // Release the search lock and reset progress
        self::$searchLock = false;
        $this->isSearching = false;
        $this->searchProgress = 0;
        
        $this->messages[] = [
            'role' => 'assistant',
            'content' => 'Search cancelled. How else can I help you?',
            'timestamp' => now()
        ];
    }

    public function render()
    {
        return view('livewire.parts.chat-search');
    }
}
