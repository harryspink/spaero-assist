<div>
    @if($team)
        <!-- HEADER -->
        <x-header title="{{ $team->name }}: Settings" subtitle="Configure laboratory settings" separator back="{{ route('teams.index') }}" progress-indicator>
            <x-slot:actions>
                <x-button label="Save Settings" wire:click="saveSettings" icon="o-check" class="btn-primary" spinner />
            </x-slot:actions>
        </x-header>

        <!-- SETTINGS FORM -->
        <x-card shadow>
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Laboratory Settings</h3>
                    <p class="text-base-content/70 mb-4">Configure your laboratory's name and other settings.</p>
                </div>
                
                <x-input 
                    label="Laboratory Name" 
                    wire:model="name" 
                    placeholder="Enter laboratory name" 
                    hint="The name of your laboratory"
                    icon="o-user-group"
                />
                
                <div class="border-t border-base-300 my-6 pt-6">
                    <h3 class="text-lg font-semibold mb-4">Slide Viewer Settings</h3>
                    <p class="text-base-content/70 mb-4">Configure the URL for the slide viewer that will be used by this laboratory.</p>
                </div>
                
                <x-input 
                    label="Slide Viewer URL" 
                    wire:model="slide_viewer_url" 
                    placeholder="https://example.com/slide-viewer" 
                    type="url" 
                    hint="Enter the full URL to your slide viewer application"
                    icon="o-link"
                />
                
                <div class="pt-4">
                    <x-button label="Save Settings" wire:click="saveSettings" icon="o-check" class="btn-primary" spinner />
                </div>
            </div>
        </x-card>
    @else
        <!-- NO LABORATORY FOUND -->
        <x-card shadow>
            <div class="text-center py-8">
                <x-icon name="o-exclamation-triangle" class="w-16 h-16 mx-auto text-warning" />
                <h3 class="text-xl font-semibold mt-4">Laboratory Not Found</h3>
                <p class="text-base-content/70 mt-2">The laboratory you're looking for doesn't exist or you don't have access to it.</p>
                <div class="mt-6">
                    <x-button label="Back to Laboratories" link="{{ route('teams.index') }}" icon="o-arrow-left" class="btn-primary" />
                </div>
            </div>
        </x-card>
    @endif
</div>
