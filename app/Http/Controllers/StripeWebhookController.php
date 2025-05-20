<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    /**
     * Handle a Stripe webhook call.
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook.secret');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $webhookSecret
            );
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            // Invalid payload
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $this->handleEvent($event);

        return response()->json(['success' => true]);
    }

    /**
     * Handle the event.
     */
    protected function handleEvent($event): void
    {
        switch ($event->type) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $this->handleSubscriptionChange($event->data->object);
                break;
            case 'invoice.payment_succeeded':
                $this->handleSuccessfulPayment($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->handleFailedPayment($event->data->object);
                break;
            default:
                // Unhandled event type
                Log::info('Unhandled Stripe event: ' . $event->type);
        }
    }

    /**
     * Handle subscription changes.
     */
    protected function handleSubscriptionChange($subscription): void
    {
        $team = Team::where('subscription_id', $subscription->id)->first();

        if (!$team) {
            Log::error('Team not found for subscription: ' . $subscription->id);
            return;
        }

        $team->update([
            'subscription_status' => $subscription->status,
            'trial_ends_at' => $subscription->trial_end ? 
                \Carbon\Carbon::createFromTimestamp($subscription->trial_end) : 
                null,
        ]);

        Log::info('Team subscription updated: ' . $team->id . ' - Status: ' . $subscription->status);
    }

    /**
     * Handle successful payment.
     */
    protected function handleSuccessfulPayment($invoice): void
    {
        if (!isset($invoice->subscription)) {
            return;
        }

        $team = Team::where('subscription_id', $invoice->subscription)->first();

        if (!$team) {
            Log::error('Team not found for invoice: ' . $invoice->id);
            return;
        }

        Log::info('Payment succeeded for team: ' . $team->id . ' - Invoice: ' . $invoice->id);
    }

    /**
     * Handle failed payment.
     */
    protected function handleFailedPayment($invoice): void
    {
        if (!isset($invoice->subscription)) {
            return;
        }

        $team = Team::where('subscription_id', $invoice->subscription)->first();

        if (!$team) {
            Log::error('Team not found for invoice: ' . $invoice->id);
            return;
        }

        Log::warning('Payment failed for team: ' . $team->id . ' - Invoice: ' . $invoice->id);
    }
}
