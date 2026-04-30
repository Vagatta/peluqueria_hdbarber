<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        Stripe::setApiVersion('2024-06-20');
    }

    public function createCheckoutSession(Appointment $appointment, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $service = $appointment->service;
        $currency = config('services.stripe.currency', 'eur');

        $session = CheckoutSession::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $appointment->user->email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $service->price_cents,
                    'product_data' => [
                        'name' => $service->name,
                        'description' => "Cita {$appointment->start_at->format('d/m/Y H:i')}",
                    ],
                ],
            ]],
            'metadata' => [
                'appointment_id' => $appointment->id,
                'user_id' => $appointment->user_id,
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        Payment::create([
            'user_id' => $appointment->user_id,
            'appointment_id' => $appointment->id,
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_intent_id' => $session->payment_intent,
            'amount_cents' => $service->price_cents,
            'currency' => $currency,
            'status' => 'pending',
            'metadata' => ['mode' => 'checkout'],
        ]);

        return $session;
    }

    public function createPaymentIntent(Appointment $appointment): PaymentIntent
    {
        $currency = config('services.stripe.currency', 'eur');

        $intent = PaymentIntent::create([
            'amount' => $appointment->service->price_cents,
            'currency' => $currency,
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'appointment_id' => $appointment->id,
                'user_id' => $appointment->user_id,
            ],
        ]);

        Payment::updateOrCreate(
            ['stripe_payment_intent_id' => $intent->id],
            [
                'user_id' => $appointment->user_id,
                'appointment_id' => $appointment->id,
                'amount_cents' => $appointment->service->price_cents,
                'currency' => $currency,
                'status' => 'pending',
                'metadata' => ['mode' => 'intent'],
            ]
        );

        return $intent;
    }

    public function constructWebhookEvent(string $payload, string $signature): \Stripe\Event
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook.secret'),
            (int) config('services.stripe.webhook.tolerance', 300)
        );
    }
}
