<div class="flex min-h-screen items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">Forgot Password</h1>
            <p class="text-base-content/70 mt-2">Enter your email and we'll send you a link to reset your password.</p>
        </div>

        <x-card shadow>
            <form wire:submit="sendResetLink" class="space-y-4">
                <x-input 
                    label="Email" 
                    wire:model="email" 
                    placeholder="Enter your email" 
                    type="email" 
                    required 
                    icon="o-envelope"
                />
                
                <x-button label="Send Reset Link" type="submit" class="btn-primary w-full" spinner />
                
                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-sm text-primary hover:underline">
                        Back to Login
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</div>
