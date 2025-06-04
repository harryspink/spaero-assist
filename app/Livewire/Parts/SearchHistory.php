<?php

namespace App\Livewire\Parts;

use App\Models\SearchHistory as SearchHistoryModel;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class SearchHistory extends Component
{
    use Toast, WithPagination;

    public string $search = '';
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public int $perPage = 10;

    // Clear search filter
    public function clear(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    // Delete a search history entry
    public function deleteHistory($id): void
    {
        $history = SearchHistoryModel::where('team_id', auth()->user()->current_team_id)
            ->where('id', $id)
            ->first();

        if ($history) {
            $history->delete();
            $this->success('Search history entry deleted.', position: 'toast-bottom');
        }
    }

    // Re-run a previous search
    public function rerunSearch($searchTerm)
    {
        return redirect()->route('parts.search', ['q' => $searchTerm]);
    }

    // View search results
    public function viewResults($id)
    {
        $history = SearchHistoryModel::where('team_id', auth()->user()->current_team_id)
            ->where('id', $id)
            ->first();

        if ($history && $history->search_results) {
            return redirect()->route('parts.search-history.results', ['id' => $id]);
        } else {
            $this->warning('No stored results available for this search.', position: 'toast-bottom');
        }
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'search_term', 'label' => 'Search Term', 'class' => 'w-48'],
            ['key' => 'user.name', 'label' => 'Searched By', 'class' => 'w-32'],
            ['key' => 'results_count', 'label' => 'Results', 'class' => 'w-24 text-center'],
            ['key' => 'success', 'label' => 'Status', 'class' => 'w-24 text-center'],
            ['key' => 'created_at', 'label' => 'Date', 'class' => 'w-32'],
            ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-32 text-right'],
        ];
    }

    public function render()
    {
        $histories = SearchHistoryModel::where('team_id', auth()->user()->current_team_id)
            ->with('user')
            ->when($this->search, function ($query) {
                $query->where('search_term', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);

        return view('livewire.parts.search-history', [
            'histories' => $histories,
            'headers' => $this->headers(),
        ]);
    }
}
