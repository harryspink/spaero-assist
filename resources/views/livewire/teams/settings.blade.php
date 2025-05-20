<div>
    @if($team)
        <!-- HEADER -->
        <x-header title="{{ $team->name }}: Settings" subtitle="Configure organisation settings" separator back="{{ route('teams.index') }}" progress-indicator>
            <x-slot:actions>
                <x-button label="Save Settings" wire:click="saveSettings" icon="o-check" class="btn-primary" spinner />
            </x-slot:actions>
        </x-header>

        <!-- SETTINGS FORM -->
        <x-card shadow>
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Organisation Settings</h3>
                    <p class="text-base-content/70 mb-4">Configure your organisation's name and other settings.</p>
                </div>
                
                <x-input 
                    label="Organisation Name" 
                    wire:model="name" 
                    placeholder="Enter organisation name" 
                    hint="The name of your organisation"
                    icon="o-user-group"
                />
                
                <div class="border-t border-base-300 my-6 pt-6">
                    <h3 class="text-lg font-semibold mb-4">Part Viewer Settings</h3>
                    <p class="text-base-content/70 mb-4">Configure the URL for the part viewer that will be used by this organisation.</p>
                </div>
                
                <x-input 
                    label="Part Viewer URL" 
                    wire:model="slide_viewer_url" 
                    placeholder="https://example.com/part-viewer" 
                    type="url" 
                    hint="Enter the full URL to your part viewer application"
                    icon="o-link"
                />
                
                <div class="border-t border-base-300 my-6 pt-6">
                    <h3 class="text-lg font-semibold mb-4">Billing Management</h3>
                    <p class="text-base-content/70 mb-4">Manage your organisation's subscription and billing information.</p>
                    
                    <div class="flex items-center justify-between p-4 border border-base-300 rounded-lg">
                        <div>
                            <h4 class="font-medium">Subscription Status</h4>
                            <p class="text-base-content/70 text-sm mt-1">
                                @if($team->subscribed())
                                    Your subscription is active.
                                @elseif($team->onTrial())
                                    Your trial ends on {{ $team->trial_ends_at->format('M d, Y') }}.
                                @elseif($team->subscription_status === 'canceled')
                                    Your subscription has been canceled.
                                @else
                                    You don't have an active subscription.
                                @endif
                            </p>
                        </div>
                        <x-button 
                            label="Manage Billing" 
                            link="{{ route('teams.billing', $team->id) }}" 
                            icon="o-credit-card" 
                            class="btn-outline btn-primary" 
                        />
                    </div>
                </div>
                
                <div class="pt-4">
                    <x-button label="Save Settings" wire:click="saveSettings" icon="o-check" class="btn-primary" spinner />
                </div>
            </div>
        </x-card>
    @else
        <!-- NO ORGANISATION FOUND -->
        <x-card shadow>
            <div class="text-center py-8">
                <x-icon name="o-exclamation-triangle" class="w-16 h-16 mx-auto text-warning" />
                <h3 class="text-xl font-semibold mt-4">Organisation Not Found</h3>
                <p class="text-base-content/70 mt-2">The organisation you're looking for doesn't exist or you don't have access to it.</p>
                <div class="mt-6">
                    <x-button label="Back to Organisations" link="{{ route('teams.index') }}" icon="o-arrow-left" class="btn-primary" />
                </div>
            </div>
        </x-card>
    @endif
</div>
