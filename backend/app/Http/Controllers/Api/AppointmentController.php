<?php

namespace App\Http\Controllers\Api;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Employee;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointmentService) {}

    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'date' => ['required', 'date'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $employee = isset($data['employee_id']) ? Employee::findOrFail($data['employee_id']) : null;
        $date = Carbon::parse($data['date'])->startOfDay();

        return response()->json([
            'slots' => $this->appointmentService->availableSlots($service, $date, $employee),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = Appointment::with(['service', 'employee', 'payment']);

        if (! $user->isAdmin()) {
            $q->where('user_id', $user->id);
        }

        return response()->json($q->orderByDesc('start_at')->limit(200)->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'start_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $start = Carbon::parse($data['start_at']);
        $end = $start->copy()->addMinutes($service->duration_minutes);

        $this->appointmentService->ensureSlotFree($start, $end, $data['employee_id'] ?? null);

        $appointment = Appointment::create([
            'user_id' => $request->user()->id,
            'service_id' => $service->id,
            'employee_id' => $data['employee_id'] ?? null,
            'start_at' => $start,
            'end_at' => $end,
            'status' => Appointment::STATUS_PENDING,
            'payment_status' => Appointment::PAY_UNPAID,
            'notes' => isset($data['notes']) ? strip_tags($data['notes']) : null,
        ]);

        return response()->json($appointment->load(['service', 'employee']), 201);
    }

    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwn($request, $appointment);
        return response()->json($appointment->load(['service', 'employee', 'payment']));
    }

    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwn($request, $appointment);

        if (! $appointment->isCancellable() && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'No se puede cancelar (menos de 2h o ya cancelada)'], 422);
        }

        $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
        return response()->json($appointment);
    }

    public function adminStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'     => ['required', 'exists:users,id'],
            'service_id'  => ['required', 'exists:services,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'start_at'    => ['required', 'date'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $start   = Carbon::parse($data['start_at']);
        $end     = $start->copy()->addMinutes($service->duration_minutes);

        $this->appointmentService->ensureSlotFree($start, $end, $data['employee_id'] ?? null);

        $appointment = Appointment::create([
            'user_id'        => $data['user_id'],
            'service_id'     => $service->id,
            'employee_id'    => $data['employee_id'] ?? null,
            'start_at'       => $start,
            'end_at'         => $end,
            'status'         => Appointment::STATUS_CONFIRMED,
            'payment_status' => Appointment::PAY_UNPAID,
            'notes'          => isset($data['notes']) ? strip_tags($data['notes']) : null,
        ]);

        return response()->json($appointment->load(['service', 'employee']), 201);
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        // Admin only changes — also allows reschedule (start_at / employee_id)
        $data = $request->validate([
            'status'         => ['sometimes', 'in:pending,confirmed,cancelled,completed,no_show'],
            'payment_status' => ['sometimes', 'in:unpaid,paid,refunded,failed'],
            'employee_id'    => ['sometimes', 'nullable', 'exists:employees,id'],
            'start_at'       => ['sometimes', 'date'],
            'notes'          => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        // Recalculate end_at if start_at is being changed
        if (isset($data['start_at'])) {
            $service = $appointment->service ?? Service::findOrFail($appointment->service_id);
            $start   = Carbon::parse($data['start_at']);
            $end     = $start->copy()->addMinutes($service->duration_minutes);

            $this->appointmentService->ensureSlotFree($start, $end, $data['employee_id'] ?? $appointment->employee_id);

            $data['start_at'] = $start;
            $data['end_at']   = $end;
        }

        $appointment->update($data);
        return response()->json($appointment->fresh(['service', 'employee']));
    }

    private function authorizeOwn(Request $request, Appointment $appointment): void
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $appointment->user_id === $user->id, 403);
    }
}
