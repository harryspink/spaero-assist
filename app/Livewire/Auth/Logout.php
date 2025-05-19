<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Logout extends Component
{
    use Toast;

    public function logout()
    {
        Auth::logout();
        
        session()->invalidate();
        session()->regenerateToken();
        
        $this->success('Successfully logged out!', position: 'toast-bottom');
        
        return redirect()->route('login');
    }
    
    public function render()
    {
        return view('livewire.auth.logout');
    }
}
