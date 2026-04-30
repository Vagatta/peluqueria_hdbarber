<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@hdbarber.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin12345'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente@hdbarber.test'],
            [
                'name' => 'Cliente Demo',
                'password' => Hash::make('Cliente12345'),
                'role' => User::ROLE_CLIENT,
                'phone' => '600000000',
            ]
        );

        $services = [
            ['name' => 'Corte clásico', 'duration_minutes' => 30, 'price_cents' => 1500, 'description' => 'Corte de cabello tradicional con tijera y máquina.'],
            ['name' => 'Corte + Barba', 'duration_minutes' => 45, 'price_cents' => 2500, 'description' => 'Corte completo más arreglo de barba.'],
            ['name' => 'Afeitado tradicional', 'duration_minutes' => 30, 'price_cents' => 1800, 'description' => 'Afeitado a navaja con toallas calientes.'],
            ['name' => 'Tinte', 'duration_minutes' => 60, 'price_cents' => 3500, 'description' => 'Coloración profesional.'],
            ['name' => 'Corte infantil', 'duration_minutes' => 25, 'price_cents' => 1200, 'description' => 'Corte para niños hasta 12 años.'],
        ];

        foreach ($services as $s) {
            Service::updateOrCreate(['name' => $s['name']], $s + ['active' => true]);
        }

        $employees = [
            ['name' => 'Carlos', 'position' => 'Senior Barber'],
            ['name' => 'Marta', 'position' => 'Estilista'],
        ];
        $defaultHours = [
            'mon' => [['start' => '09:30', 'end' => '14:00'], ['start' => '16:00', 'end' => '20:00']],
            'tue' => [['start' => '09:30', 'end' => '14:00'], ['start' => '16:00', 'end' => '20:00']],
            'wed' => [['start' => '09:30', 'end' => '14:00'], ['start' => '16:00', 'end' => '20:00']],
            'thu' => [['start' => '09:30', 'end' => '14:00'], ['start' => '16:00', 'end' => '20:00']],
            'fri' => [['start' => '09:30', 'end' => '14:00'], ['start' => '16:00', 'end' => '20:30']],
            'sat' => [['start' => '10:00', 'end' => '14:00']],
            'sun' => [],
        ];

        foreach ($employees as $e) {
            $emp = Employee::updateOrCreate(
                ['name' => $e['name']],
                $e + ['active' => true, 'working_hours' => $defaultHours]
            );
            $emp->services()->sync(Service::pluck('id')->all());
        }
    }
}
