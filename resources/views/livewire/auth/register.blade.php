<div class="flex min-h-screen items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">Create an Account</h1>
            <p class="text-base-content/70 mt-2">Sign up to get started with our platform.</p>
        </div>

        <x-card shadow>
            <form wire:submit="register" class="space-y-4">
                <x-input 
                    label="Name" 
                    wire:model="name" 
                    placeholder="Enter your name" 
                    required 
                    icon="o-user"
                />
                
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
                
                <x-input 
                    label="Confirm Password" 
                    wire:model="password_confirmation" 
                    placeholder="Confirm your password" 
                    type="password" 
                    required 
                    icon="o-key"
                />
                
                <x-button label="Register" type="submit" class="btn-primary w-full" spinner />
                
                <div class="text-center mt-4">
                    <span class="text-sm">Already have an account?</span>
                    <a href="{{ route('login') }}" class="text-sm text-primary hover:underline">
                        Login
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</div>
