<div>
    @if($team)
        <!-- HEADER -->
        <x-header title="{{ $team->name }}: Credentials" subtitle="Manage your organisation's third-party site credentials" separator back="{{ route('teams.index') }}" progress-indicator>
            <x-slot:actions>
                <x-button label="Add Credentials" @click="$wire.showCredentialModal = true" icon="o-plus" class="btn-primary" />
            </x-slot:actions>
        </x-header>

        <!-- CREDENTIALS LIST -->
        <x-card shadow>
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Third-Party Site Credentials</h3>
                    <p class="text-base-content/70 mb-4">Manage credentials for external systems and services used by your organisation.</p>
                </div>
                
                @if(count($sites) === 0)
                    <div class="text-center py-8">
                        <x-icon name="o-exclamation-triangle" class="w-16 h-16 mx-auto text-warning" />
                        <h3 class="text-xl font-semibold mt-4">No Sites Configured</h3>
                        <p class="text-base-content/70 mt-2">No third-party sites have been configured in the system.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th>Site</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sites as $siteKey => $site)
                                    <tr>
                                        <td class="font-medium">{{ $site['name'] }}</td>
                                        <td>{{ $site['description'] ?? 'No description available' }}</td>
                                        <td>
                                            @if(isset($teamCredentials[$siteKey]))
                                                <span class="badge badge-success">Configured</span>
                                            @else
                                                <span class="badge badge-ghost">Not Configured</span>
                                            @endif
                                        </td>
                                        <td class="flex gap-2">
                                            <x-button 
                                                icon="o-pencil-square" 
                                                wire:click="selectSite('{{ $siteKey }}')" 
                                                class="btn-sm btn-outline" 
                                                tooltip="{{ isset($teamCredentials[$siteKey]) ? 'Edit Credentials' : 'Add Credentials' }}" 
                                            />
                                            
                                            @if(isset($teamCredentials[$siteKey]))
                                                <x-button 
                                                    icon="o-trash" 
                                                    wire:click="deleteCredentials('{{ $siteKey }}')" 
                                                    wire:confirm="Are you sure you want to delete these credentials?" 
                                                    class="btn-sm btn-outline btn-error" 
                                                    tooltip="Delete Credentials" 
                                                />
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </x-card>
        
        <!-- CREDENTIAL MODAL -->
        <x-modal wire:model="showCredentialModal" title="{{ isset($teamCredentials[$selectedSite]) ? 'Edit Credentials' : 'Add Credentials' }}" subtitle="{{ $selectedSite ? $sites[$selectedSite]['name'] : '' }}">
            @if($selectedSite && isset($sites[$selectedSite]))
                <div class="space-y-4">
                    <p class="text-base-content/70">{{ $sites[$selectedSite]['description'] ?? 'Enter the credentials for this site.' }}</p>
                    
                    @foreach($sites[$selectedSite]['fields'] as $field)
                        @php
                            $fieldName = $field['name'];
                            $fieldType = $field['type'];
                            $isRequired = isset($field['required']) ? $field['required'] : true;
                            $placeholder = $field['placeholder'] ?? '';
                            $helpText = $field['help_text'] ?? '';
                        @endphp
                        
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">{{ $field['label'] }} {{ $isRequired ? '*' : '' }}</span>
                            </label>
                            
                            @if($fieldType === 'password')
                                <input 
                                    type="password" 
                                    wire:model="credentials.{{ $fieldName }}" 
                                    class="input input-bordered w-full" 
                                    placeholder="{{ $placeholder }}" 
                                    {{ $isRequired ? 'required' : '' }}
                                />
                            @else
                                <input 
                                    type="{{ $fieldType }}" 
                                    wire:model="credentials.{{ $fieldName }}" 
                                    class="input input-bordered w-full" 
                                    placeholder="{{ $placeholder }}" 
                                    {{ $isRequired ? 'required' : '' }}
                                />
                            @endif
                            
                            @if($helpText)
                                <label class="label">
                                    <span class="label-text-alt text-base-content/70">{{ $helpText }}</span>
                                </label>
                            @endif
                            
                            @error("credentials.$fieldName")
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    @endforeach
                </div>
                
                <x-slot:actions>
                    <x-button label="Cancel" @click="$wire.showCredentialModal = false" />
                    <x-button label="Save Credentials" wire:click="saveCredentials" class="btn-primary" spinner />
                </x-slot:actions>
            @else
                <div class="text-center py-6">
                    <x-icon name="o-exclamation-triangle" class="w-12 h-12 mx-auto text-warning" />
                    <p class="mt-4">No site selected or site configuration not found.</p>
                </div>
                
                <x-slot:actions>
                    <x-button label="Close" @click="$wire.showCredentialModal = false" />
                </x-slot:actions>
            @endif
        </x-modal>
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
