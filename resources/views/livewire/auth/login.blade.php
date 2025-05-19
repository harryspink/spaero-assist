<div class="flex min-h-screen items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">Login</h1>
            <p class="text-base-content/70 mt-2">Welcome back! Please sign in to your account.</p>
        </div>

        <x-card shadow>
            <form wire:submit="login" class="space-y-4">
                <x-input 
                    label="Email" 
                    wire:model="email" 
                    placeholder="Enter your email" 
                    type="email" 
                    required 
                    icon="o-envelope"
                />
                
                <x-input 
                    label="Password" 
                    wire:model="password" 
                    placeholder="Enter your password" 
                    type="password" 
                    required 
                    icon="o-key"
                />
                
                <div class="flex items-center justify-between">
                    <x-checkbox label="Remember me" wire:model="remember" />
                    <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">
                        Forgot password?
                    </a>
                </div>
                
                <x-button label="Sign In" type="submit" class="btn-primary w-full" spinner />
                
                <div class="text-center mt-4">
                    <span class="text-sm">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-sm text-primary hover:underline">
                        Register
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</div>
