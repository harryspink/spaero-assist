<?php

namespace App\Services;

use App\Models\Team;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentMethod;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    /**
     * Create a new Stripe service instance.
     */
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe customer for the team.
     */
    public function createCustomer(Team $team): string
    {
        try {
            $customer = Customer::create([
                'name' => $team->name,
                'email' => $team->owner->email,
                'metadata' => [
                    'team_id' => $team->id,
                ],
            ]);

            $team->update(['stripe_id' => $customer->id]);

            return $customer->id;
        } catch (ApiErrorException $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Get or create a Stripe customer for the team.
     */
    public function getOrCreateCustomer(Team $team): string
    {
        if ($team->hasStripeId()) {
            return $team->stripe_id;
        }

        return $this->createCustomer($team);
    }

    /**
     * Update the team's payment method.
     */
    public function updatePaymentMethod(Team $team, string $paymentMethodId): void
    {
        try {
            $customerId = $this->getOrCreateCustomer($team);

            // Attach the payment method to the customer
            PaymentMethod::attach($paymentMethodId, [
                'customer' => $customerId,
            ]);

            // Set as the default payment method
            Customer::update($customerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            // Get the payment method details
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);

            // Update the team's payment method information
            $team->update([
                'pm_type' => $paymentMethod->type,
                'pm_last_four' => $paymentMethod->card->last4,
            ]);
        } catch (ApiErrorException $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Create a subscription for the team.
     */
    public function createSubscription(Team $team, string $planId, int $trialDays = 0): void
    {
        try {
            $customerId = $this->getOrCreateCustomer($team);

            $subscription = Subscription::create([
                'customer' => $customerId,
                'items' => [
                    ['price' => $planId],
                ],
                'trial_period_days' => $trialDays,
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $team->update([
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'trial_ends_at' => $trialDays > 0 ? now()->addDays($trialDays) : null,
            ]);
        } catch (ApiErrorException $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Cancel the team's subscription.
     */
    public function cancelSubscription(Team $team): void
    {
        try {
            if (!$team->hasSubscription()) {
                return;
            }

            $subscription = Subscription::retrieve($team->subscription_id);
            $subscription->cancel();

            $team->update([
                'subscription_status' => 'canceled',
            ]);
        } catch (ApiErrorException $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Resume the team's subscription.
     */
    public function resumeSubscription(Team $team): void
    {
        try {
            if (!$team->subscription_id || $team->subscription_status !== 'canceled') {
                return;
            }

            $subscription = Subscription::retrieve($team->subscription_id);
            
            if ($subscription->cancel_at_period_end) {
                $subscription = Subscription::update($team->subscription_id, [
                    'cancel_at_period_end' => false,
                ]);

                $team->update([
                    'subscription_status' => $subscription->status,
                ]);
            }
        } catch (ApiErrorException $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Get the team's subscription.
     */
    public function getSubscription(Team $team): ?object
    {
        try {
            if (!$team->hasSubscription()) {
                return null;
            }

            return Subscription::retrieve($team->subscription_id);
        } catch (ApiErrorException $e) {
            report($e);
            return null;
        }
    }

    /**
     * Sync the team's subscription status with Stripe.
     */
    public function syncSubscriptionStatus(Team $team): void
    {
        try {
            if (!$team->hasSubscription()) {
                return;
            }

            $subscription = Subscription::retrieve($team->subscription_id);

            $team->update([
                'subscription_status' => $subscription->status,
                'trial_ends_at' => $subscription->trial_end ? 
                    \Carbon\Carbon::createFromTimestamp($subscription->trial_end) : 
                    null,
            ]);
        } catch (ApiErrorException $e) {
            report($e);
            throw $e;
        }
    }
}
