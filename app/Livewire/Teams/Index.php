<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast;

    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    // Delete action
    public function delete($id): void
    {
        $team = Team::findOrFail($id);
        
        if ($team->isOwner(auth()->user())) {
            $team->delete();
            $this->success('Organisation deleted successfully.', position: 'toast-bottom');
        } else {
            $this->error('You do not have permission to delete this organisation.', position: 'toast-bottom');
        }
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            ['key' => 'members_count', 'label' => 'Members', 'class' => 'w-20'],
            ['key' => 'role', 'label' => 'Your Role', 'sortable' => false],
        ];
    }

    public function teams(): Collection
    {
        $user = auth()->user();
        
        if (!$user) {
            return collect([]);
        }
        
        return collect($user->allTeams())
            ->map(function ($team) use ($user) {
                $teamWithRole = $team;
                $teamWithRole['members_count'] = $team->users()->count();
                $teamWithRole['role'] = $team->isOwner($user) ? 'Owner' : $user->teams()->where('team_id', $team->id)->first()->pivot->role;
                return $teamWithRole;
            })
            ->sortBy([[...array_values($this->sortBy)]])
            ->when($this->search, function (Collection $collection) {
                return $collection->filter(fn($item) => str($item['name'])->contains($this->search, true));
            });
    }

    public function switchTeam($teamId): void
    {
        $team = Team::findOrFail($teamId);
        $user = auth()->user();
        
        if ($user->switchTeam($team)) {
            $this->success('Switched to organisation: ' . $team->name, position: 'toast-bottom');
            $this->redirect(request()->header('Referer'));
        } else {
            $this->error('Failed to switch organisations.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        $user = auth()->user();
        
        return view('livewire.teams.index', [
            'teams' => $this->teams(),
            'headers' => $this->headers(),
            'currentTeam' => $user ? $user->currentTeam : null,
        ]);
    }
}
