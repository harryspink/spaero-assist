<div>
    <x-dropdown>
        <x-slot:trigger>
            <x-button class="btn-ghost gap-2">
                <span>{{ $currentTeam ? $currentTeam->name : 'Select Team' }}</span>
                <x-icon name="o-chevron-down" class="h-4 w-4" />
            </x-button>
        </x-slot:trigger>
        
        @foreach($teams as $team)
            <x-menu-item 
                :title="$team->name" 
                :active="$currentTeam && $currentTeam->id === $team->id"
                wire:click="switchTeam({{ $team->id }})" 
                wire:key="team-{{ $team->id }}"
            />
        @endforeach
        
        <div class="divider my-1"></div>
        
        <x-menu-item title="Manage Teams" link="{{ route('teams.index') }}" icon="o-cog-6-tooth" />
    </x-dropdown>
</div>
