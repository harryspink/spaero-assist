<?php

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public $currentTeam;

    public function mount()
    {
        $this->currentTeam = auth()->user()->currentTeam;
    }

    // Clear filters
    public function clear(): void
    {
        $this->reset('search');
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            ['key' => 'email', 'label' => 'Email', 'class' => 'w-64'],
            ['key' => 'role', 'label' => 'Role', 'sortable' => false],
        ];
    }

    public function teamMembers(): Collection
    {
        if (!$this->currentTeam) {
            return collect([]);
        }

        return $this->currentTeam->users()
            ->with('teams')
            ->get()
            ->map(function ($user) {
                $user->role = $user->teams()->where('team_id', $this->currentTeam->id)->first()->pivot->role;
                return $user;
            })
            ->sortBy([[...array_values($this->sortBy)]])
            ->when($this->search, function (Collection $collection) {
                return $collection->filter(fn($item) => 
                    str($item['name'])->contains($this->search, true) || 
                    str($item['email'])->contains($this->search, true)
                );
            });
    }

    public function with(): array
    {
        return [
            'members' => $this->teamMembers(),
            'headers' => $this->headers(),
            'currentTeam' => $this->currentTeam,
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Dashboard" subtitle="{{ $currentTeam ? 'Team: ' . $currentTeam->name : 'No team selected' }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search members..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            @if($currentTeam)
                <x-button label="Manage Team" link="{{ route('teams.members', $currentTeam->id) }}" icon="o-users" class="btn-primary" />
            @endif
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
        </x-slot:actions>
    </x-header>

    @if($currentTeam)
        <!-- TEAM INFO -->
        <x-card shadow class="mb-6">
            <div class="flex flex-col md:flex-row gap-4 justify-between">
                <div>
                    <h2 class="text-xl font-bold">{{ $currentTeam->name }}</h2>
                    <p class="text-base-content/70">{{ $currentTeam->description ?: 'No description provided' }}</p>
                </div>
                <div>
                    <div class="badge badge-primary">{{ count($members) }} Members</div>
                    <div class="badge badge-secondary">
                        Your Role: {{ $currentTeam->isOwner(auth()->user()) ? 'Owner' : auth()->user()->teams()->where('team_id', $currentTeam->id)->first()->pivot->role }}
                    </div>
                </div>
            </div>
        </x-card>

        <!-- MEMBERS TABLE -->
        <x-card shadow>
            <h3 class="text-lg font-semibold mb-4">Team Members</h3>
            <x-table :headers="$headers" :rows="$members" :sort-by="$sortBy">
                @scope('cell_role', $member)
                    <x-badge :value="$member->role" :color="$member->role === 'owner' ? 'success' : ($member->role === 'admin' ? 'warning' : 'info')" />
                @endscope
            </x-table>
        </x-card>
    @else
        <!-- NO TEAM SELECTED -->
        <x-card shadow>
            <div class="text-center py-8">
                <x-icon name="o-user-group" class="w-16 h-16 mx-auto text-base-content/30" />
                <h3 class="text-xl font-semibold mt-4">No Team Selected</h3>
                <p class="text-base-content/70 mt-2">Please create or join a team to get started.</p>
                <div class="mt-6">
                    <x-button label="Create Team" link="{{ route('teams.create') }}" icon="o-plus" class="btn-primary" />
                </div>
            </div>
        </x-card>
    @endif

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
