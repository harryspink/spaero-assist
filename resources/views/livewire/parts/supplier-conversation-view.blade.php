<div wire:poll.10s>
    <!-- HEADER -->
    <x-header title="Supplier Conversation" separator>
        <x-slot:actions>
            <x-button 
                label="Back to Conversations" 
                icon="o-arrow-left" 
                link="{{ route('parts.supplier-conversations') }}"
                class="btn-ghost"
            />
            @if($conversation->status === 'active')
                <x-button 
                    label="Close Conversation" 
                    icon="o-x-mark" 
                    wire:click="closeConversation"
                    class="btn-error btn-sm"
                    wire:confirm="Are you sure you want to close this conversation?"
                />
            @else
                <x-button 
                    label="Reopen Conversation" 
                    icon="o-arrow-path" 
                    wire:click="reopenConversation"
                    class="btn-success btn-sm"
                />
            @endif
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Conversation Info -->
        <div class="lg:col-span-1">
            <x-card title="Conversation Details">
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-500">Part Number</label>
                        <p class="font-semibold">{{ $conversation->part_number }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Supplier</label>
                        <p class="font-semibold">{{ $conversation->supplier_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Email</label>
                        <p class="text-sm">{{ $conversation->supplier_email }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Subject</label>
                        <p class="text-sm">{{ $conversation->subject }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Status</label>
                        <p>
                            @if($conversation->status === 'active')
                                <x-badge value="Active" class="badge-success" />
                            @else
                                <x-badge value="Closed" class="badge-error" />
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Started</label>
                        <p class="text-sm">{{ $conversation->created_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Messages -->
        <div class="lg:col-span-3">
            <x-card class="h-[calc(100vh-280px)]">
                <!-- Messages Container -->
                <div class="h-full flex flex-col">
                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                        @foreach($this->getMessages() as $message)
                            <div class="flex {{ $message->direction === 'sent' ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-2xl {{ $message->direction === 'sent' ? 'order-2' : '' }}">
                                    <div class="flex items-start gap-3">
                                        @if($message->direction === 'received')
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full bg-base-300 flex items-center justify-center">
                                                    <x-icon name="o-building-office" class="w-5 h-5" />
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="flex-1">
                                            <div class="rounded-lg px-4 py-2 {{ $message->direction === 'sent' ? 'bg-primary text-primary-content' : 'bg-base-200' }}">
                                                <p class="whitespace-pre-wrap">{{ $message->message }}</p>
                                            </div>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-base-content/50 {{ $message->direction === 'sent' ? 'justify-end' : '' }}">
                                                <span>{{ $message->created_at->format('M d, g:i A') }}</span>
                                                @if($message->direction === 'sent' && $message->user)
                                                    <span>• {{ $message->user->name }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($message->direction === 'sent')
                                            <div class="flex-shrink-0 order-1">
                                                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                                                    <x-icon name="o-user" class="w-5 h-5 text-primary-content" />
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Message Input -->
                    @if($conversation->status === 'active')
                        <div class="border-t border-base-300 p-4">
                            <form wire:submit="sendMessage" class="flex gap-2">
                                <x-textarea 
                                    wire:model="newMessage"
                                    placeholder="Type your message..."
                                    rows="3"
                                    class="flex-1"
                                />
                                <x-button 
                                    type="submit" 
                                    icon="o-paper-airplane" 
                                    class="btn-primary self-end"
                                    :disabled="empty(trim($newMessage))"
                                    spinner
                                />
                            </form>
                        </div>
                    @else
                        <div class="border-t border-base-300 p-4 text-center text-gray-500">
                            This conversation is closed. Reopen it to send new messages.
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-scroll to bottom on new messages
    document.addEventListener('livewire:initialized', () => {
        const scrollToBottom = () => {
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        };
        
        // Initial scroll
        scrollToBottom();
        
        // Scroll on updates
        Livewire.hook('morph.updated', ({ el, component }) => {
            if (component.fingerprint.name.includes('supplier-conversation-view')) {
                scrollToBottom();
            }
        });
    });
</script>
@endpush
