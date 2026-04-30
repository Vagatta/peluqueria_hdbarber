<?php

namespace App\Http\Controllers\Api;

use App\Models\Appointment;
use App\Models\Payment;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    public function __invoke(Request $request): JsonResponse
    {
        $signature = $request->header('Stripe-Signature', '');
        $payload = $request->getContent();

        try {
            $event = $this->stripe->constructWebhookEvent($payload, $signature);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature failed', ['err' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'payment_intent.succeeded' => $this->handleIntentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->handleIntentFailed($event->data->object),
            'charge.refunded' => $this->handleRefunded($event->data->object),
            default => Log::info('Stripe event ignored', ['type' => $event->type]),
        };

        return response()->json(['received' => true]);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $payment = Payment::where('stripe_checkout_session_id', $session->id)->first();
        if (! $payment) return;

        $payment->update([
            'status' => 'succeeded',
            'stripe_payment_intent_id' => $session->payment_intent ?? $payment->stripe_payment_intent_id,
        ]);
        $this->markAppointmentPaid($payment);
    }

    private function handleIntentSucceeded(object $intent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $intent->id)->first();
        if (! $payment) return;
        $payment->update(['status' => 'succeeded']);
        $this->markAppointmentPaid($payment);
    }

    private function handleIntentFailed(object $intent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $intent->id)->first();
        if (! $payment) return;
        $payment->update(['status' => 'failed']);
        if ($payment->appointment) {
            $payment->appointment->update(['payment_status' => Appointment::PAY_FAILED]);
        }
    }

    private function handleRefunded(object $charge): void
    {
        $intentId = $charge->payment_intent ?? null;
        if (! $intentId) return;
        $payment = Payment::where('stripe_payment_intent_id', $intentId)->first();
        if (! $payment) return;
        $payment->update(['status' => 'refunded']);
        if ($payment->appointment) {
            $payment->appointment->update(['payment_status' => Appointment::PAY_REFUNDED]);
        }
    }

    private function markAppointmentPaid(Payment $payment): void
    {
        if ($payment->appointment) {
            $payment->appointment->update([
                'payment_status' => Appointment::PAY_PAID,
                'status' => Appointment::STATUS_CONFIRMED,
            ]);
        }
    }
}
