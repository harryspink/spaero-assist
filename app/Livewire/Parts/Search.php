<?php

namespace App\Livewire\Parts;

use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Mary\Traits\Toast;

class Search extends Component
{
    use Toast;

    public string $search = '';
    public array $results = [];
    public bool $isLoading = false;
    public bool $hasSearched = false;
    public ?string $error = null;

    public function mount()
    {
        $this->search = request()->query('q', '');
        if ($this->search) {
            $this->searchParts();
        }
    }

    public function searchParts()
    {
        if (empty($this->search)) {
            $this->error('Please enter a search term.', position: 'toast-bottom');
            return;
        }

        $this->isLoading = true;
        $this->error = null;
        $this->hasSearched = true;
        $this->results = [];

        try {
            $team = auth()->user()->currentTeam;
            
            if (!$team || !$team->slide_viewer_url) {
                $this->error = 'Your team does not have a part viewer URL configured. Please ask your team administrator to set it up.';
                $this->isLoading = false;
                return;
            }

            $baseUrl = 'https://slides.pathtech.pathhub.co.uk/api/cases/';
            $response = Http::get($baseUrl . $this->search);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success' && isset($data['slides']) && is_array($data['slides'])) {
                    $this->results = $data['slides'];
                    
                    if (empty($this->results)) {
                        $this->error = 'No parts found for this case ID.';
                    }
                } else {
                    $this->error = 'Invalid response format from the API.';
                }
            } else {
                $this->error = 'Failed to fetch results. Status code: ' . $response->status();
            }
        } catch (\Exception $e) {
            $this->error = 'An error occurred: ' . $e->getMessage();
        }

        $this->isLoading = false;
    }

    public function viewPart($partUrlPath, $partId = '', $caseNo = '', $path = '')
    {
        $team = auth()->user()->currentTeam;
        
        if (!$team || !$team->slide_viewer_url) {
            $this->error('Your team does not have a part viewer URL configured.', position: 'toast-bottom');
            return;
        }

        // Get the case number from the search term if not provided
        if (empty($caseNo)) {
            $caseNo = $this->search;
        }

        // Redirect to the part viewer page with the part URL as a query parameter
        return redirect()->route('parts.view', [
            'url' => $partUrlPath,
            'id' => $partId,
            'case' => $caseNo,
            'path' => $path
        ]);
    }

    public function render()
    {
        return view('livewire.parts.search');
    }
}
