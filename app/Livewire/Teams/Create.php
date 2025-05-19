<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Illuminate\Support\Str;
use Livewire\Component;
use Mary\Traits\Toast;

class Create extends Component
{
    use Toast;

    public string $name = '';
    public string $description = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function createTeam()
    {
        $this->validate();

        $user = auth()->user();
        
        if (!$user) {
            $this->error('You must be logged in to create a team.', position: 'toast-bottom');
            return redirect()->route('login');
        }

        $team = Team::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name) . '-' . Str::random(5),
            'description' => $this->description,
            'owner_id' => $user->id,
        ]);

        // Add the current user to the team
        $team->users()->attach($user, ['role' => 'owner']);

        // Set this as the user's current team if they don't have one
        if (!$user->currentTeam) {
            $user->switchTeam($team);
        }

        $this->success('Team created successfully!', position: 'toast-bottom');
        $this->reset(['name', 'description']);
        
        return redirect()->route('teams.index');
    }

    public function render()
    {
        return view('livewire.teams.create');
    }
}
