<div>
    @if($team)
        <!-- HEADER -->
        <x-header title="{{ $team->name }}: Billing" subtitle="Manage your organisation's subscription" separator back="{{ route('teams.index') }}" progress-indicator>
            <x-slot:actions>
                @if($team->hasSubscription())
                    @if($team->subscription_status === 'canceled')
                        <x-button label="Resume Subscription" wire:click="resumeSubscription" icon="o-arrow-path" class="btn-primary" spinner />
                    @else
                        <x-button label="Cancel Subscription" wire:click="cancelSubscription" icon="o-x-mark" class="btn-error" spinner />
                    @endif
                @endif
            </x-slot:actions>
        </x-header>

        <!-- CURRENT SUBSCRIPTION -->
        <x-card shadow class="mb-6">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Current Subscription</h3>
                    <p class="text-base-content/70 mb-4">Your organisation's current subscription status and details.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="border border-base-300 rounded-lg p-4">
                        <h4 class="font-medium text-base mb-2">Status</h4>
                        <div class="flex items-center">
                            @if($team->subscribed())
                                <span class="badge badge-success">{{ $team->subscription_status }}</span>
                            @elseif($team->onTrial())
                                <span class="badge badge-warning">Trial</span>
                            @elseif($team->subscription_status === 'canceled')
                                <span class="badge badge-error">{{ $team->subscription_status }}</span>
                            @else
                                <span class="badge badge-ghost">No Subscription</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="border border-base-300 rounded-lg p-4">
                        <h4 class="font-medium text-base mb-2">Payment Method</h4>
                        <div>
                            @if($team->pm_type)
                                <div class="flex items-center">
                                    <x-icon name="o-credit-card" class="w-5 h-5 mr-2" />
                                    <span>{{ ucfirst($team->pm_type) }} ending in {{ $team->pm_last_four }}</span>
                                </div>
                            @else
                                <span class="text-base-content/70">No payment method</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="border border-base-300 rounded-lg p-4">
                        <h4 class="font-medium text-base mb-2">Trial Ends</h4>
                        <div>
                            @if($team->trial_ends_at)
                                <div class="flex items-center">
                                    <x-icon name="o-calendar" class="w-5 h-5 mr-2" />
                                    <span>{{ $team->trial_ends_at->format('M d, Y') }}</span>
                                </div>
                            @else
                                <span class="text-base-content/70">No trial</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- SUBSCRIPTION PLANS -->
        @if(!$team->hasSubscription() || $team->subscription_status === 'canceled')
            <x-card shadow class="mb-6">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Subscription Plans</h3>
                        <p class="text-base-content/70 mb-4">Choose a subscription plan for your organisation.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($plans as $plan)
                            <div 
                                class="border rounded-lg p-6 cursor-pointer transition-all duration-200 {{ $selectedPlan === $plan['id'] ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-primary/50' }}"
                                wire:click="selectPlan('{{ $plan['id'] }}')"
                            >
                                <div class="flex justify-between items-start mb-4">
                                    <h4 class="text-lg font-semibold">{{ $plan['name'] }}</h4>
                                    @if($selectedPlan === $plan['id'])
                                        <x-icon name="o-check-circle" class="w-6 h-6 text-primary" />
                                    @endif
                                </div>
                                <div class="mb-4">
                                    <span class="text-2xl font-bold">{{ $plan['price'] }}</span>
                                    <span class="text-base-content/70">/{{ $plan['interval'] }}</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach($plan['features'] as $feature)
                                        <div class="flex items-center">
                                            <x-icon name="o-check" class="w-5 h-5 mr-2 text-success" />
                                            <span>{{ $feature }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-card>

            <!-- PAYMENT METHOD -->
            <x-card shadow class="mb-6">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Payment Method</h3>
                        <p class="text-base-content/70 mb-4">Add a payment method to complete your subscription.</p>
                    </div>
                    
                    <div>
                        <x-input 
                            label="Cardholder Name" 
                            wire:model="cardholderName" 
                            placeholder="Enter cardholder name" 
                            icon="o-user"
                        />
                    </div>
                    
                    <div id="card-element" class="p-3 border border-base-300 rounded-lg">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    
                    @if($stripeError)
                        <div class="text-error text-sm">{{ $stripeError }}</div>
                    @endif
                    
                    <div class="pt-4">
                        <x-button 
                            label="Subscribe" 
                            wire:click="subscribe" 
                            icon="o-credit-card" 
                            class="btn-primary" 
                            spinner 
                            :disabled="!$selectedPlan || !$paymentMethod"
                        />
                    </div>
                </div>
            </x-card>
        @endif
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

    @push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const stripe = Stripe('{{ $stripeKey }}');
            const elements = stripe.elements();
            const cardElement = elements.create('card');
            
            cardElement.mount('#card-element');
            
            cardElement.on('change', function(event) {
                if (event.error) {
                    @this.set('stripeError', event.error.message);
                } else {
                    @this.set('stripeError', null);
                }
            });
            
            @this.on('subscribe', async () => {
                const cardholderName = @this.get('cardholderName');
                
                if (!cardholderName) {
                    @this.set('stripeError', 'Please enter the cardholder name');
                    return;
                }
                
                const { paymentMethod, error } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        name: cardholderName,
                    },
                });
                
                if (error) {
                    @this.set('stripeError', error.message);
                } else {
                    @this.dispatch('stripe-payment-method-selected', paymentMethod.id);
                }
            });
        });
    </script>
    @endpush
</div>
