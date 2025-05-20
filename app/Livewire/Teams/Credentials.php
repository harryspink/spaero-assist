<?php

namespace App\Livewire\Teams;

use App\Models\SiteCredential;
use App\Models\Team;
use Livewire\Component;
use Mary\Traits\Toast;

class Credentials extends Component
{
    use Toast;

    public $team;
    public $sites = [];
    public $selectedSite = null;
    public $credentials = [];
    public $showCredentialModal = false;

    protected function rules()
    {
        $rules = [
            'credentials' => 'required|array',
        ];

        if ($this->selectedSite) {
            $siteConfig = config("site_credentials.sites.{$this->selectedSite}");
            
            if ($siteConfig && isset($siteConfig['fields'])) {
                foreach ($siteConfig['fields'] as $field) {
                    $fieldName = $field['name'];
                    $rule = 'nullable';
                    
                    if (isset($field['required']) && $field['required']) {
                        $rule = 'required';
                    }
                    
                    if ($field['type'] === 'email') {
                        $rule .= '|email';
                    } elseif ($field['type'] === 'url') {
                        $rule .= '|url';
                    }
                    
                    $rules["credentials.{$fieldName}"] = $rule;
                }
            }
        }

        return $rules;
    }

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

            // Load available sites from config
            $this->sites = config('site_credentials.sites', []);
            
        } catch (\Exception $e) {
            $this->team = null;
            $this->error('Organisation not found.', position: 'toast-bottom');
            return redirect()->route('teams.index');
        }
    }

    public function selectSite($siteKey)
    {
        $this->selectedSite = $siteKey;
        $this->resetCredentials();
        
        // Load existing credentials if available
        $siteCredential = $this->team->getSiteCredential($siteKey);
        
        if ($siteCredential) {
            $this->credentials = $siteCredential->credentials;
        }
        
        $this->showCredentialModal = true;
    }

    public function resetCredentials()
    {
        $this->credentials = [];
        $this->resetValidation();
    }

    public function saveCredentials()
    {
        $this->validate();
        
        if (!$this->selectedSite) {
            $this->error('No site selected.', position: 'toast-bottom');
            return;
        }
        
        try {
            // Find or create site credential
            $siteCredential = $this->team->getSiteCredential($this->selectedSite);
            
            if (!$siteCredential) {
                $siteCredential = new SiteCredential([
                    'team_id' => $this->team->id,
                    'site_key' => $this->selectedSite,
                    'credentials' => [],
                ]);
            }
            
            // Update credentials
            $siteCredential->credentials = $this->credentials;
            $siteCredential->save();
            
            $this->success('Credentials saved successfully.', position: 'toast-bottom');
            $this->showCredentialModal = false;
            
        } catch (\Exception $e) {
            $this->error('Failed to save credentials: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function deleteCredentials($siteKey)
    {
        try {
            $siteCredential = $this->team->getSiteCredential($siteKey);
            
            if ($siteCredential) {
                $siteCredential->delete();
                $this->success('Credentials deleted successfully.', position: 'toast-bottom');
            }
            
        } catch (\Exception $e) {
            $this->error('Failed to delete credentials: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        $teamCredentials = $this->team->siteCredentials()->get()->keyBy('site_key');
        
        return view('livewire.teams.credentials', [
            'team' => $this->team,
            'teamCredentials' => $teamCredentials,
        ]);
    }
}
