<div class="flex min-h-screen items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">Reset Password</h1>
            <p class="text-base-content/70 mt-2">Create a new password for your account.</p>
        </div>

        <x-card shadow>
            <form wire:submit="resetPassword" class="space-y-4">
                <input type="hidden" wire:model="token">
                
                <x-input 
                    label="Email" 
                    wire:model="email" 
                    placeholder="Enter your email" 
                    type="email" 
                    required 
                    icon="o-envelope"
                />
                
                <x-input 
                    label="New Password" 
                    wire:model="password" 
                    placeholder="Enter your new password" 
                    type="password" 
                    required 
                    icon="o-key"
                />
                
                <x-input 
                    label="Confirm Password" 
                    wire:model="password_confirmation" 
                    placeholder="Confirm your new password" 
                    type="password" 
                    required 
                    icon="o-key"
                />
                
                <x-button label="Reset Password" type="submit" class="btn-primary w-full" spinner />
                
                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-sm text-primary hover:underline">
                        Back to Login
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</div>
