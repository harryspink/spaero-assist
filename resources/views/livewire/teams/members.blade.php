<div>
    @if($team)
        <!-- HEADER -->
        <x-header title="{{ $team->name }}: Members" subtitle="Manage organisation members" separator back="{{ route('teams.index') }}" progress-indicator>
            <x-slot:middle class="!justify-end">
                <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
            </x-slot:middle>
            <x-slot:actions>
                <x-button label="Add Member" @click="$wire.drawer = true" icon="o-user-plus" class="btn-primary" />
                <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            </x-slot:actions>
        </x-header>

        <!-- TABLE  -->
        <x-card shadow>
            <x-table :headers="$headers" :rows="$members" :sort-by="$sortBy">
                @scope('actions', $member)
                    <div class="flex gap-1">
                        @if(true /*!$team->isOwner($member)*/)
                            <x-dropdown>
                                <x-slot:trigger>
                                    <x-button icon="o-ellipsis-vertical" class="btn-ghost btn-sm" />
                                </x-slot:trigger>
                                <x-menu-item title="Change Role" icon="o-user" />
                                <x-menu-item title="Remove from Organisation" icon="o-user-minus" wire:click="removeMember({{ $member->id }})" wire:confirm="Are you sure you want to remove this member from the organisation?" />
                            </x-dropdown>
                        @endif
                    </div>
                @endscope

                @scope('cell_role', $member)
                    <x-badge :value="$member->role" :color="$member->role === 'owner' ? 'success' : ($member->role === 'admin' ? 'warning' : 'info')" />
                @endscope
            </x-table>
        </x-card>

        <!-- ADD MEMBER DRAWER -->
        <x-drawer wire:model="drawer" title="Add Organisation Member" right separator with-close-button class="lg:w-1/3">
            <form wire:submit="addMember" class="space-y-4">
                <x-input label="Email Address" wire:model="email" placeholder="Enter email address" type="email" required />
                
                <x-select 
                    label="Role" 
                    wire:model="role" 
                    placeholder="Select role"
                    :options="[
                        ['value' => 'member', 'label' => 'Member'],
                        ['value' => 'admin', 'label' => 'Admin'],
                    ]"
                    required
                />
                
                <div class="flex justify-end gap-2 mt-6">
                    <x-button label="Cancel" @click="$wire.drawer = false" />
                    <x-button label="Add Member" type="submit" icon="o-user-plus" class="btn-primary" spinner />
                </div>
            </form>

            <x-slot:actions>
                <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
                <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
            </x-slot:actions>
        </x-drawer>
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
