<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Livewire\Component;
use Mary\Traits\Toast;

class Switcher extends Component
{
    use Toast;

    public function switchTeam($teamId)
    {
        $team = Team::findOrFail($teamId);
        $user = auth()->user();
        
        if ($user->switchTeam($team)) {
            $this->success('Switched to team: ' . $team->name, position: 'toast-bottom');
            $this->dispatch('team-switched');
        } else {
            $this->error('Failed to switch teams.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        $user = auth()->user();
        
        if (!$user) {
            return view('livewire.teams.switcher', [
                'teams' => collect([]),
                'currentTeam' => null,
            ]);
        }
        
        return view('livewire.teams.switcher', [
            'teams' => $user->allTeams(),
            'currentTeam' => $user->currentTeam,
        ]);
    }
}
