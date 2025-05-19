<div>
    <!-- HEADER -->
    <x-header title="Create Team" subtitle="Create a new team for collaboration" separator back="{{ route('teams.index') }}" progress-indicator>
    </x-header>

    <!-- FORM -->
    <x-card shadow>
        <form wire:submit="createTeam" class="space-y-4">
            <x-input label="Team Name" wire:model="name" placeholder="Enter team name" required />
            
            <x-textarea label="Description" wire:model="description" placeholder="Enter team description (optional)" />
            
            <div class="flex justify-end gap-2 mt-6">
                <x-button label="Cancel" link="{{ route('teams.index') }}" />
                <x-button label="Create Team" type="submit" icon="o-plus" class="btn-primary" spinner />
            </div>
        </form>
    </x-card>
</div>
