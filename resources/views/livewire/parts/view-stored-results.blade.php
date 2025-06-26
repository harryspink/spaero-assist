<div>
    <!-- HEADER -->
    <x-header title="Stored Search Results" separator>
        <x-slot:actions>
            <x-button 
                label="Back to History" 
                icon="o-arrow-left" 
                wire:click="backToHistory"
            />
            <x-button 
                label="Re-run Search" 
                icon="o-arrow-path" 
                class="btn-primary"
                wire:click="rerunSearch"
            />
        </x-slot:actions>
    </x-header>

    @if($searchHistory)
        <!-- Search Info Card -->
        <x-card class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm text-gray-500">Search Term</label>
                    <p class="font-semibold">{{ $searchHistory->search_term }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Searched By</label>
                    <p class="font-semibold">{{ $searchHistory->user->name }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Search Date</label>
                    <p class="font-semibold">{{ $searchHistory->created_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
        </x-card>
    @endif

    <!-- Results Table -->
    <x-card>
        @if($results->isEmpty())
            <div class="text-center py-12">
                <x-icon name="o-document-magnifying-glass" class="w-24 h-24 mx-auto text-base-content/20" />
                <h3 class="text-xl font-semibold mt-6">No Results Found</h3>
                <p class="text-base-content/70 mt-2">
                    This search did not have any stored results.
                </p>
            </div>
        @else
            <x-table :headers="$headers" :rows="$results" :sort-by="$sortBy">
                @scope('cell_actions', $result)
                    <x-button 
                        wire:click="startConversation({{ json_encode($result) }})"
                        icon="o-chat-bubble-left-right" 
                        class="btn-sm btn-ghost"
                        tooltip="Start conversation with supplier"
                    />
                @endscope
            </x-table>
        @endif
    </x-card>
</div>
