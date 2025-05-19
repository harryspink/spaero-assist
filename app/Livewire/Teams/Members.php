<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;

class Members extends Component
{
    use Toast;

    public $team;
    public string $email = '';
    public string $role = 'member';
    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function mount($teamId = null)
    {
        $user = auth()->user();
        
        if (!$user) {
            $this->team = null;
            return redirect()->route('login');
        }
        
        try {
            $this->team = $teamId ? Team::findOrFail($teamId) : $user->currentTeam;

            if (!$this->team) {
                $this->error('No team selected.', position: 'toast-bottom');
                return redirect()->route('teams.index');
            }
            
            if (!$user->belongsToTeam($this->team)) {
                $this->error('You do not have access to this team.', position: 'toast-bottom');
                return redirect()->route('teams.index');
            }
        } catch (\Exception $e) {
            $this->team = null;
            $this->error('Team not found.', position: 'toast-bottom');
            return redirect()->route('teams.index');
        }
    }

    // Clear filters
    public function clear(): void
    {
        $this->reset(['search']);
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        if (!$this->team) {
            return [];
        }
        
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            ['key' => 'email', 'label' => 'Email', 'class' => 'w-64'],
            ['key' => 'role', 'label' => 'Role', 'sortable' => false],
        ];
    }

    public function members(): Collection
    {
        if (!$this->team) {
            return collect([]);
        }
        
        return $this->team->users()
            ->with('teams')
            ->get()
            ->map(function ($user) {
                $user->role = $user->teams()->where('team_id', $this->team->id)->first()->pivot->role;
                return $user;
            })
            ->sortBy([[...array_values($this->sortBy)]])
            ->when($this->search, function (Collection $collection) {
                return $collection->filter(fn($item) => 
                    str($item['name'])->contains($this->search, true) || 
                    str($item['email'])->contains($this->search, true)
                );
            });
    }

    public function addMember()
    {
        if (!$this->team) {
            $this->error('No team selected.', position: 'toast-bottom');
            return redirect()->route('teams.index');
        }
        
        $this->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:member,admin',
        ]);

        $user = User::where('email', $this->email)->first();

        if (!$user) {
            $this->error('User not found.', position: 'toast-bottom');
            return;
        }

        if ($this->team->hasUser($user)) {
            $this->error('User is already a member of this team.', position: 'toast-bottom');
            return;
        }

        // Only team owner or admin can add members
        $currentUser = auth()->user();
        $currentUserRole = $currentUser->teams()->where('team_id', $this->team->id)->first()->pivot->role;
        
        if (!$this->team->isOwner($currentUser) && $currentUserRole !== 'admin') {
            $this->error('You do not have permission to add members to this team.', position: 'toast-bottom');
            return;
        }

        $this->team->users()->attach($user, ['role' => $this->role]);
        $this->success('Member added successfully.', position: 'toast-bottom');
        $this->reset(['email', 'role']);
    }

    public function updateRole($userId, $newRole)
    {
        if (!$this->team) {
            $this->error('No team selected.', position: 'toast-bottom');
            return redirect()->route('teams.index');
        }
        
        $this->validate([
            'role' => 'required|in:member,admin',
        ]);

        $user = User::findOrFail($userId);
        $currentUser = auth()->user();

        // Only team owner can change roles
        if (!$this->team->isOwner($currentUser)) {
            $this->error('Only the team owner can change roles.', position: 'toast-bottom');
            return;
        }

        // Cannot change the role of the team owner
        if ($this->team->isOwner($user)) {
            $this->error('Cannot change the role of the team owner.', position: 'toast-bottom');
            return;
        }

        $this->team->users()->updateExistingPivot($userId, ['role' => $newRole]);
        $this->success('Role updated successfully.', position: 'toast-bottom');
    }

    public function removeMember($userId)
    {
        if (!$this->team) {
            $this->error('No team selected.', position: 'toast-bottom');
            return redirect()->route('teams.index');
        }
        
        $user = User::findOrFail($userId);
        $currentUser = auth()->user();

        // Cannot remove the team owner
        if ($this->team->isOwner($user)) {
            $this->error('Cannot remove the team owner.', position: 'toast-bottom');
            return;
        }

        // Only team owner or admin can remove members
        $currentUserRole = $currentUser->teams()->where('team_id', $this->team->id)->first()->pivot->role;
        
        if (!$this->team->isOwner($currentUser) && $currentUserRole !== 'admin') {
            $this->error('You do not have permission to remove members from this team.', position: 'toast-bottom');
            return;
        }

        $this->team->users()->detach($userId);
        
        // If the removed user's current team was this team, set their current team to null
        if ($user->current_team_id === $this->team->id) {
            $user->update(['current_team_id' => null]);
        }
        
        $this->success('Member removed successfully.', position: 'toast-bottom');
    }

    public function render()
    {
        return view('livewire.teams.members', [
            'members' => $this->members(),
            'headers' => $this->headers(),
            'team' => $this->team,
        ]);
    }
}
