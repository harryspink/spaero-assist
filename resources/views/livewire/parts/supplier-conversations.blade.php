<div>
    <!-- HEADER -->
    <x-header title="Supplier Conversations" separator>
        <x-slot:middle class="!justify-end">
            <x-input 
                placeholder="Search conversations..." 
                wire:model.live.debounce="search" 
                clearable 
                icon="o-magnifying-glass" 
                class="w-96"
            />
        </x-slot:middle>
        <x-slot:actions>
            <x-select 
                wire:model.live="statusFilter" 
                :options="[
                    ['value' => 'all', 'label' => 'All Conversations'],
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'closed', 'label' => 'Closed']
                ]" 
                class="w-48"
            />
        </x-slot:actions>
    </x-header>

    <!-- TABLE -->
    <x-card>
        @if($conversations->isEmpty())
            <div class="text-center py-12">
                <x-icon name="o-chat-bubble-left-right" class="w-24 h-24 mx-auto text-base-content/20" />
                <h3 class="text-xl font-semibold mt-6">No Conversations Yet</h3>
                <p class="text-base-content/70 mt-2 max-w-md mx-auto">
                    Start a conversation with suppliers from your search results.
                </p>
                <x-button 
                    label="View Search History" 
                    icon="o-clock" 
                    class="btn-primary mt-4"
                    link="{{ route('parts.search-history') }}"
                />
            </div>
        @else
            <x-table :headers="$headers" :rows="$conversations" :sort-by="$sortBy" striped>
                @scope('cell_subject', $conversation)
                    <div class="max-w-xs truncate" title="{{ $conversation['subject'] }}">
                        {{ $conversation['subject'] }}
                    </div>
                @endscope

                @scope('cell_status', $conversation)
                    @if($conversation['status'] === 'active')
                        <x-badge value="Active" class="badge-success badge-sm" />
                    @else
                        <x-badge value="Closed" class="badge-error badge-sm" />
                    @endif
                @endscope

                @scope('cell_unread', $conversation)
                    @if($conversation['unread'] > 0)
                        <x-badge value="{{ $conversation['unread'] }}" class="badge-warning" />
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                @endscope

                @scope('cell_last_message_at', $conversation)
                    @if($conversation['last_message_at'])
                        <span class="text-sm">
                            {{ $conversation['last_message_at']->format('M d, Y') }}<br>
                            <span class="text-xs text-gray-500">{{ $conversation['last_message_at']->format('g:i A') }}</span>
                        </span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                @endscope

                @scope('cell_actions', $conversation)
                    <div class="flex justify-end gap-1">
                        <x-button 
                            icon="o-eye" 
                            link="{{ route('parts.supplier-conversation.view', $conversation['id']) }}"
                            class="btn-ghost btn-sm"
                            tooltip="View conversation"
                        />
                        <x-button 
                            icon="o-trash" 
                            wire:click="deleteConversation({{ $conversation['id'] }})"
                            wire:confirm="Are you sure you want to delete this conversation?"
                            class="btn-ghost btn-sm text-error"
                            tooltip="Delete"
                        />
                    </div>
                @endscope
            </x-table>

            <div class="mt-4">
                {{ $conversations->links() }}
            </div>
        @endif
    </x-card>
</div>
