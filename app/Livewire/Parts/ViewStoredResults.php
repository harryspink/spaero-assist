<?php

namespace App\Livewire\Parts;

use App\Models\SearchHistory;
use Livewire\Component;
use Mary\Traits\Toast;

class ViewStoredResults extends Component
{
    use Toast;

    public ?SearchHistory $searchHistory = null;
    public array $sortBy = ['column' => '1', 'direction' => 'asc'];
    
    public function mount($id)
    {
        $this->searchHistory = SearchHistory::where('team_id', auth()->user()->current_team_id)
            ->where('id', $id)
            ->with('user')
            ->first();
            
        if (!$this->searchHistory) {
            $this->error('Search history not found.', position: 'toast-bottom');
            return redirect()->route('parts.search-history');
        }
        
        if (!$this->searchHistory->search_results || count($this->searchHistory->search_results) === 0) {
            $this->error('No stored results found for this search.', position: 'toast-bottom');
            return redirect()->route('parts.search-history');
        }
    }
    
    public function backToHistory()
    {
        return redirect()->route('parts.search-history');
    }
    
    public function rerunSearch()
    {
        return redirect()->route('parts.search', ['q' => $this->searchHistory->search_term]);
    }
    
    // Table headers
    public function headers(): array
    {
        return [
            ['key' => '1', 'label' => 'Part Number', 'class' => 'w-32'],
            ['key' => '2', 'label' => 'Supplier', 'class' => 'w-40'],
            ['key' => '3', 'label' => 'Manufacturer', 'class' => 'w-40'],
            ['key' => '4', 'label' => 'Description', 'class' => 'w-48'],
            ['key' => '5', 'label' => 'Condition', 'class' => 'w-24'],
            ['key' => '6', 'label' => 'Quantity', 'class' => 'w-24'],
            ['key' => '7', 'label' => 'Location', 'class' => 'w-32'],
            ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-24'],
        ];
    }
    
    public function startConversation($rowData)
    {
        // Extract supplier info from the row data
        $partNumber = $rowData['1'] ?? '';
        $supplierName = $rowData['2'] ?? '';
        
        // Build URL with query parameters
        $url = route('parts.supplier-conversation.create') . '?' . http_build_query([
            'search_history_id' => $this->searchHistory->id,
            'part_number' => $partNumber,
            'supplier_name' => $supplierName,
        ]);
        
        return redirect($url);
    }
    
    public function render()
    {
        if (!$this->searchHistory) {
            return view('livewire.parts.view-stored-results', [
                'results' => collect([]),
                'headers' => $this->headers(),
            ]);
        }
        
        // Process stored results into a format compatible with the table
        $processedResults = collect($this->searchHistory->search_results)
            ->filter(fn($row) => count($row) > 5) // Filter out empty or header rows
            ->map(function($row) {
                // Map the array data to a keyed array for the table
                return [
                    'id' => $row[0] ?? '',
                    '1' => $row[1] ?? '', // Part Number
                    '2' => $row[2] ?? '', // Supplier
                    '3' => $row[3] ?? '', // Manufacturer
                    '4' => $row[4] ?? '', // Description
                    '5' => $row[5] ?? '', // Condition
                    '6' => $row[7] ?? '', // Quantity
                    '7' => $row[11] ?? '', // Location
                    'raw' => $row, // Keep raw data for actions
                ];
            });
        
        return view('livewire.parts.view-stored-results', [
            'results' => $processedResults,
            'headers' => $this->headers(),
        ]);
    }
}
