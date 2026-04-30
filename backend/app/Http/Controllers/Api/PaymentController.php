<?php

namespace App\Http\Controllers\Api;

use App\Models\Appointment;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    public function checkout(Request $request, Appointment $appointment): JsonResponse
    {
        abort_unless($appointment->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        if ($appointment->payment_status === Appointment::PAY_PAID) {
            return response()->json(['message' => 'Ya pagado'], 422);
        }

        $spa = rtrim((string) env('SPA_URL', 'http://localhost:5173'), '/');
        $session = $this->stripe->createCheckoutSession(
            $appointment,
            $spa.'/payment/success?session_id={CHECKOUT_SESSION_ID}',
            $spa.'/payment/cancel'
        );

        return response()->json([
            'url' => $session->url,
            'id' => $session->id,
        ]);
    }

    public function intent(Request $request, Appointment $appointment): JsonResponse
    {
        abort_unless($appointment->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
        $intent = $this->stripe->createPaymentIntent($appointment);

        return response()->json([
            'client_secret' => $intent->client_secret,
            'publishable_key' => config('services.stripe.key'),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = $user->isAdmin()
            ? \App\Models\Payment::query()->with(['user', 'appointment.service'])
            : $user->payments()->with('appointment.service');

        return response()->json($q->orderByDesc('created_at')->limit(200)->get());
    }
}
