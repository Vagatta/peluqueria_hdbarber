<?php

namespace App\Http\Controllers\Api;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $tz = config('app.timezone_display', 'Europe/Madrid');
        $today = now($tz)->startOfDay();
        $weekStart = now($tz)->startOfWeek();
        $monthStart = now($tz)->startOfMonth();

        return response()->json([
            'appointments_today' => Appointment::whereDate('start_at', $today)->count(),
            'appointments_week' => Appointment::where('start_at', '>=', $weekStart)->count(),
            'revenue_month_cents' => (int) Appointment::where('start_at', '>=', $monthStart)
                ->where('payment_status', Appointment::PAY_PAID)
                ->whereIn('status', [Appointment::STATUS_CONFIRMED, Appointment::STATUS_COMPLETED])
                ->join('services', 'appointments.service_id', '=', 'services.id')
                ->sum('services.price_cents'),
            'clients_total' => User::where('role', User::ROLE_CLIENT)->count(),
            'upcoming' => Appointment::with(['user:id,name', 'service:id,name', 'employee:id,name'])
                ->where('start_at', '>=', now())
                ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
                ->orderBy('start_at')
                ->limit(10)->get(),
        ]);
    }

    public function clients(): JsonResponse
    {
        return response()->json(
            User::where('role', User::ROLE_CLIENT)
                ->withCount('appointments')
                ->orderByDesc('created_at')
                ->limit(500)->get()
        );
    }

    public function employeesIndex(): JsonResponse
    {
        return response()->json(Employee::with('services:id,name')->orderBy('name')->get());
    }

    public function employeesStore(Request $request): JsonResponse
    {
        $data = $this->employeeRules($request);
        $emp = Employee::create($data);
        if (isset($data['service_ids'])) $emp->services()->sync($data['service_ids']);
        return response()->json($emp->load('services'), 201);
    }

    public function employeesUpdate(Request $request, Employee $employee): JsonResponse
    {
        $data = $this->employeeRules($request, true);
        $employee->update($data);
        if (isset($data['service_ids'])) $employee->services()->sync($data['service_ids']);
        return response()->json($employee->load('services'));
    }

    public function employeesDestroy(Employee $employee): JsonResponse
    {
        $employee->delete();
        return response()->json(['message' => 'ok']);
    }

    private function employeeRules(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';
        return $request->validate([
            'name' => [$req, 'string', 'max:120'],
            'position' => ['nullable', 'string', 'max:120'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'working_hours' => ['nullable', 'array'],
            'active' => ['boolean'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);
    }
}
