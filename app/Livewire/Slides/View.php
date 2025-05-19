<?php

namespace App\Livewire\Slides;

use Livewire\Component;
use Mary\Traits\Toast;

class View extends Component
{
    use Toast;

    public string $slideUrl = '';
    public string $slideId = '';
    public string $caseNo = '';
    public string $path = '';

    public function mount()
    {
        // Get parameters from the query string
        $this->slideUrl = request()->query('url', '');
        $this->slideId = request()->query('id', '');
        $this->caseNo = request()->query('case', '');
        $this->path = request()->query('path', '');
        
        if (empty($this->slideUrl)) {
            $this->error('No slide URL provided.', position: 'toast-bottom');
            return redirect()->route('slides.search');
        }
    }

    public function backToSearch()
    {
        return redirect()->route('slides.search', ['q' => $this->caseNo]);
    }

    public function render()
    {
        return view('livewire.slides.view');
    }
}
