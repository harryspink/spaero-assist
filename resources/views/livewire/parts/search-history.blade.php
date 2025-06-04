<div>
    <!-- HEADER -->
    <x-header title="Search History" separator>
        <x-slot:middle class="!justify-end">
            <x-input 
                placeholder="Search history..." 
                wire:model.live.debounce="search" 
                clearable 
                icon="o-magnifying-glass" 
            />
        </x-slot:middle>
        <x-slot:actions>
            <x-button 
                label="New Search" 
                icon="o-plus" 
                class="btn-primary"
                link="{{ route('parts.search') }}"
            />
        </x-slot:actions>
    </x-header>

    <!-- TABLE -->
    <x-card>
        @if($histories->isEmpty())
            <div class="text-center py-12">
                <x-icon name="o-clock" class="w-24 h-24 mx-auto text-base-content/20" />
                <h3 class="text-xl font-semibold mt-6">No Search History</h3>
                <p class="text-base-content/70 mt-2 max-w-md mx-auto">
                    Your team's search history will appear here after performing searches.
                </p>
                <x-button 
                    label="Start Searching" 
                    icon="o-magnifying-glass" 
                    class="btn-primary mt-4"
                    link="{{ route('parts.search') }}"
                />
            </div>
        @else
            <x-table :headers="$headers" :rows="$histories" :sort-by="$sortBy" with-pagination per-page="perPage">
                @scope('cell_success', $history)
                    @if($history->success)
                        <x-badge value="Success" class="badge-success" />
                    @else
                        <x-badge value="Failed" class="badge-error" />
                    @endif
                @endscope

                @scope('cell_results_count', $history)
                    <div class="text-center">
                        {{ $history->results_count }}
                    </div>
                @endscope

                @scope('cell_created_at', $history)
                    <span class="text-sm">
                        {{ $history->created_at->format('M d, Y') }}<br>
                        <span class="text-xs text-gray-500">{{ $history->created_at->format('g:i A') }}</span>
                    </span>
                @endscope

                @scope('cell_actions', $history)
                    <div class="flex justify-end gap-1">
                        <x-button 
                            icon="o-arrow-path" 
                            wire:click="rerunSearch('{{ $history->search_term }}')"
                            class="btn-ghost btn-sm"
                            tooltip="Re-run search"
                        />
                        @if($history->search_results && count($history->search_results) > 0)
                            <x-button 
                                icon="o-eye" 
                                wire:click="viewResults({{ $history->id }})"
                                class="btn-ghost btn-sm"
                                tooltip="View stored results"
                            />
                        @endif
                        <x-button 
                            icon="o-trash" 
                            wire:click="deleteHistory({{ $history->id }})"
                            wire:confirm="Are you sure you want to delete this search history?"
                            class="btn-ghost btn-sm text-error"
                            tooltip="Delete"
                        />
                    </div>
                @endscope
            </x-table>
        @endif
    </x-card>
</div>
