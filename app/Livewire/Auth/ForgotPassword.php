<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Component;
use Mary\Traits\Toast;

class ForgotPassword extends Component
{
    use Toast;

    public string $email = '';

    protected $rules = [
        'email' => 'required|email',
    ];

    public function sendResetLink()
    {
        $this->validate();

        $status = Password::sendResetLink(
            ['email' => $this->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->success(__($status), position: 'toast-bottom');
            $this->reset('email');
        } else {
            $this->error(__($status), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->layout('components.layouts.guest');
    }
}
