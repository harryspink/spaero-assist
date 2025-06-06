<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Livewire\Component;
use Mary\Traits\Toast;

class Settings extends Component
{
    use Toast;

    public $team;
    public string $name = '';
    public string $slide_viewer_url = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'slide_viewer_url' => 'nullable|url',
    ];

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
                $this->error('No organisation selected.', position: 'toast-bottom');
                return redirect()->route('teams.index');
            }
            
            if (!$user->belongsToTeam($this->team)) {
                $this->error('You do not have access to this organisation.', position: 'toast-bottom');
                return redirect()->route('teams.index');
            }

            $this->name = $this->team->name;
            $this->slide_viewer_url = $this->team->slide_viewer_url ?? '';
            
        } catch (\Exception $e) {
            $this->team = null;
            $this->error('Organisation not found.', position: 'toast-bottom');
            return redirect()->route('teams.index');
        }
    }

    public function saveSettings()
    {
        if (!$this->team) {
            $this->error('No organisation selected.', position: 'toast-bottom');
            return redirect()->route('teams.index');
        }

        $this->validate();

        $this->team->update([
            'name' => $this->name,
            'slide_viewer_url' => $this->slide_viewer_url,
        ]);

        $this->success('Organisation settings updated successfully.', position: 'toast-bottom');
    }

    public function render()
    {
        return view('livewire.teams.settings', [
            'team' => $this->team,
        ]);
    }
}
