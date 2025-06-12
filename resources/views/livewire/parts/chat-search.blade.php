<div wire:poll.5s="checkSearchStatus" class="flex flex-col h-full">
    <!-- Header -->
    <x-header title="AI Parts Search Assistant" separator :progress-indicator="$isSearching">
        <x-slot:middle class="!justify-end">
            <x-button 
                label="Traditional Search" 
                icon="o-magnifying-glass" 
                link="/parts/search" 
                class="btn-ghost"
            />
        </x-slot:middle>
    </x-header>

    <!-- Chat Container -->
    <div class="flex-1 flex flex-col">
        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
            @foreach($messages as $message)
                <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-3xl {{ $message['role'] === 'user' ? 'order-2' : '' }}">
                        <div class="flex items-start gap-3">
                            @if($message['role'] === 'assistant')
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                                        <x-icon name="o-cpu-chip" class="w-5 h-5 text-white" />
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex-1">
                                <div class="rounded-lg px-4 py-2 {{ $message['role'] === 'user' ? 'bg-primary text-primary-content' : 'bg-base-200' }}">
                                    <div class="prose prose-sm max-w-none {{ $message['role'] === 'user' ? 'prose-invert' : '' }}">
                                        {!! Str::markdown($message['content']) !!}
                                    </div>
                                </div>
                                <div class="text-xs text-base-content/50 mt-1 {{ $message['role'] === 'user' ? 'text-right' : '' }}">
                                    {{ $message['timestamp']->format('g:i A') }}
                                </div>
                            </div>

                            @if($message['role'] === 'user')
                                <div class="flex-shrink-0 order-1">
                                    <div class="w-8 h-8 rounded-full bg-base-300 flex items-center justify-center">
                                        <x-icon name="o-user" class="w-5 h-5" />
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            @if($isTyping)
                <div class="flex justify-start">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                                <x-icon name="o-cpu-chip" class="w-5 h-5 text-white" />
                            </div>
                        </div>
                        <div class="bg-base-200 rounded-lg px-4 py-2">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-base-content/50 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                                <div class="w-2 h-2 bg-base-content/50 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                                <div class="w-2 h-2 bg-base-content/50 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($isSearching)
                <div class="flex justify-start">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                                <x-icon name="o-cpu-chip" class="w-5 h-5 text-white" />
                            </div>
                        </div>
                        <div class="bg-base-200 rounded-lg px-4 py-3 max-w-md">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="loading loading-spinner loading-sm text-primary"></span>
                                <span class="text-sm">Searching PartsBase...</span>
                            </div>
                            
                            <!-- Progress bar -->
                            <div class="w-full">
                                <div class="bg-base-300 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: {{ $searchProgress }}%"></div>
                                </div>
                                <p class="text-xs text-base-content/50 mt-1">{{ round($searchProgress) }}% complete</p>
                            </div>
                            
                            <x-button 
                                wire:click="cancelSearch" 
                                class="btn-error btn-xs mt-2"
                                icon="o-x-mark"
                            >
                                Cancel
                            </x-button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Input Area -->
        <div class="border-t border-base-300 p-4">
            <form wire:submit="sendMessage" class="flex gap-2">
                <x-input 
                    wire:model="userInput"
                    placeholder="Ask me to search for a part (e.g., 'Find part ABC123')..."
                    class="flex-1"
                    :disabled="$isSearching"
                    autofocus
                />
                <x-button 
                    type="submit" 
                    icon="o-paper-airplane" 
                    class="btn-primary"
                    :disabled="$isSearching || empty(trim($userInput))"
                    spinner
                />
            </form>
            <div class="mt-2 text-xs text-base-content/50">
                Powered by OpenAI • Type naturally, like "I need pricing for part XYZ789"
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-scroll to bottom when new messages are added
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el, component }) => {
            const messagesContainer = document.getElementById('chat-messages');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });
    });
</script>

<style>
    /* Custom animation keyframes for typing indicator */
    @keyframes bounce {
        0%, 60%, 100% {
            transform: translateY(0);
        }
        30% {
            transform: translateY(-10px);
        }
    }
</style>
@endpush
