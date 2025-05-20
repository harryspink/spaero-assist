<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Services\StripeService;
use Livewire\Component;
use Mary\Traits\Toast;

class Billing extends Component
{
    use Toast;

    public $team;
    public $plans = [];
    public $selectedPlan = null;
    public $paymentMethod = null;
    public $cardholderName = '';
    public $stripeError = null;

    protected $listeners = [
        'stripe-payment-method-selected' => 'handlePaymentMethodSelected',
    ];

    public function mount($teamId = null)
    {
        $user = auth()->user();
        
        if (!$user) {
            $this->team = null;
            return redirect()->route('login');
        }
        
        try {
            $this->team = $teamId ? Team::findOrFail($teamId) : $user->currentTeam;
            
            if (!$this->team) {
                $this->error('No organisation selected.', position: 'toast-bottom');
                return redirect()->route('teams.index');
            }
            
            if (!$user->belongsToTeam($this->team)) {
                $this->error('You do not have access to this organisation.', position: 'toast-bottom');
                return redirect()->route('teams.index');
            }

            // Define available plans
            $this->plans = [
                'price_basic' => [
                    'id' => 'price_basic',
                    'name' => 'Basic',
                    'price' => '$9.99',
                    'interval' => 'monthly',
                    'features' => [
                        'Feature 1',
                        'Feature 2',
                        'Feature 3',
                    ],
                ],
                'price_pro' => [
                    'id' => 'price_pro',
                    'name' => 'Professional',
                    'price' => '$19.99',
                    'interval' => 'monthly',
                    'features' => [
                        'Feature 1',
                        'Feature 2',
                        'Feature 3',
                        'Feature 4',
                        'Feature 5',
                    ],
                ],
                'price_enterprise' => [
                    'id' => 'price_enterprise',
                    'name' => 'Enterprise',
                    'price' => '$49.99',
                    'interval' => 'monthly',
                    'features' => [
                        'Feature 1',
                        'Feature 2',
                        'Feature 3',
                        'Feature 4',
                        'Feature 5',
                        'Feature 6',
                        'Feature 7',
                    ],
                ],
            ];
            
        } catch (\Exception $e) {
            $this->team = null;
            $this->error('Organisation not found.', position: 'toast-bottom');
            return redirect()->route('teams.index');
        }
    }

    public function selectPlan($planId)
    {
        $this->selectedPlan = $planId;
    }

    public function handlePaymentMethodSelected($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function subscribe()
    {
        if (!$this->team) {
            $this->error('No organisation selected.', position: 'toast-bottom');
            return;
        }

        if (!$this->selectedPlan) {
            $this->error('Please select a plan.', position: 'toast-bottom');
            return;
        }

        if (!$this->paymentMethod) {
            $this->error('Please add a payment method.', position: 'toast-bottom');
            return;
        }

        try {
            $stripeService = app(StripeService::class);
            
            // Update payment method
            $stripeService->updatePaymentMethod($this->team, $this->paymentMethod);
            
            // Create subscription
            $stripeService->createSubscription($this->team, $this->selectedPlan);
            
            $this->success('Subscription created successfully.', position: 'toast-bottom');
            
            // Reset form
            $this->paymentMethod = null;
            $this->selectedPlan = null;
            $this->stripeError = null;
            
            // Refresh team data
            $this->team->refresh();
            
        } catch (\Exception $e) {
            $this->stripeError = $e->getMessage();
            $this->error('Failed to create subscription: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function cancelSubscription()
    {
        if (!$this->team) {
            $this->error('No organisation selected.', position: 'toast-bottom');
            return;
        }

        if (!$this->team->hasSubscription()) {
            $this->error('No active subscription found.', position: 'toast-bottom');
            return;
        }

        try {
            $stripeService = app(StripeService::class);
            $stripeService->cancelSubscription($this->team);
            
            $this->success('Subscription cancelled successfully.', position: 'toast-bottom');
            
            // Refresh team data
            $this->team->refresh();
            
        } catch (\Exception $e) {
            $this->error('Failed to cancel subscription: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function resumeSubscription()
    {
        if (!$this->team) {
            $this->error('No organisation selected.', position: 'toast-bottom');
            return;
        }

        if (!$this->team->subscription_id || $this->team->subscription_status !== 'canceled') {
            $this->error('No canceled subscription found.', position: 'toast-bottom');
            return;
        }

        try {
            $stripeService = app(StripeService::class);
            $stripeService->resumeSubscription($this->team);
            
            $this->success('Subscription resumed successfully.', position: 'toast-bottom');
            
            // Refresh team data
            $this->team->refresh();
            
        } catch (\Exception $e) {
            $this->error('Failed to resume subscription: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.teams.billing', [
            'team' => $this->team,
            'plans' => $this->plans,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }
}
