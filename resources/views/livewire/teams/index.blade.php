<div>
    <!-- HEADER -->
    <x-header title="Teams" subtitle="Manage your teams" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Create Team" link="{{ route('teams.create') }}" icon="o-plus" class="btn-primary" />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table :headers="$headers" :rows="$teams" :sort-by="$sortBy">
            @scope('actions', $team)
                <div class="flex gap-1">
                    <x-button icon="o-arrow-path" wire:click="switchTeam({{ $team['id'] }})" spinner class="btn-ghost btn-sm" tooltip="Switch to this team" />
                    <x-button icon="o-users" link="{{ route('teams.members', $team['id']) }}" class="btn-ghost btn-sm" tooltip="Manage members" />
                    @if($team['role'] === 'Owner')
                        <x-button icon="o-trash" wire:click="delete({{ $team['id'] }})" wire:confirm="Are you sure you want to delete this team?" spinner class="btn-ghost btn-sm text-error" tooltip="Delete team" />
                    @endif
                </div>
            @endscope

            @scope('cell_role', $team)
                <x-badge :value="$team['role']" :color="$team['role'] === 'Owner' ? 'success' : ($team['role'] === 'admin' ? 'warning' : 'info')" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
