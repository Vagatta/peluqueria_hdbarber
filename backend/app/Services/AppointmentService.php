<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * Get available slots for a service on a given date.
     *
     * @return array<int, array{start:string,end:string,employee_id:?int}>
     */
    public function availableSlots(Service $service, Carbon $date, ?Employee $employee = null): array
    {
        $duration = $service->duration_minutes;
        $slotStep = 15;

        $employees = $employee
            ? collect([$employee])
            : Employee::where('active', true)->get();

        if ($employees->isEmpty()) {
            // Allow booking without specific employee using shop hours
            return $this->shopHourSlots($date, $duration, $slotStep, null);
        }

        $slots = [];
        foreach ($employees as $emp) {
            $slots = array_merge($slots, $this->shopHourSlots($date, $duration, $slotStep, $emp));
        }

        // Deduplicate by start time, prefer earliest available employee
        $unique = collect($slots)->unique('start')->sortBy('start')->values()->all();
        return $unique;
    }

    private function shopHourSlots(Carbon $date, int $duration, int $step, ?Employee $emp): array
    {
        $dayKey = strtolower($date->format('D')); // mon, tue...
        $hours = $emp?->working_hours[$dayKey] ?? [['start' => '09:00', 'end' => '20:00']];

        if (empty($hours)) {
            return [];
        }

        $busy = $this->busyRanges($date, $emp);
        $slots = [];

        foreach ($hours as $range) {
            $cursor = $date->copy()->setTimeFromTimeString($range['start']);
            $end = $date->copy()->setTimeFromTimeString($range['end']);

            while ($cursor->copy()->addMinutes($duration)->lte($end)) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addMinutes($duration);

                if (! $this->overlapsAny($slotStart, $slotEnd, $busy) && $slotStart->isFuture()) {
                    $slots[] = [
                        'start' => $slotStart->toIso8601String(),
                        'end' => $slotEnd->toIso8601String(),
                        'employee_id' => $emp?->id,
                    ];
                }
                $cursor->addMinutes($step);
            }
        }

        return $slots;
    }

    private function busyRanges(Carbon $date, ?Employee $emp): Collection
    {
        $q = Appointment::query()
            ->whereDate('start_at', $date->toDateString())
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
            ->where('end_at', '>', now()); // Solo citas futuras o en curso

        if ($emp) {
            $q->where('employee_id', $emp->id);
        }

        return $q->get(['start_at', 'end_at']);
    }

    private function overlapsAny(Carbon $start, Carbon $end, Collection $busy): bool
    {
        foreach ($busy as $b) {
            if ($start->lt($b->end_at) && $end->gt($b->start_at)) {
                return true;
            }
        }
        return false;
    }

    public function ensureSlotFree(Carbon $start, Carbon $end, ?int $employeeId): void
    {
        $q = Appointment::query()
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start);

        if ($employeeId) {
            $q->where('employee_id', $employeeId);
        }

        if ($q->exists()) {
            throw ValidationException::withMessages([
                'start_at' => 'El horario seleccionado ya no está disponible.',
            ]);
        }
    }
}
