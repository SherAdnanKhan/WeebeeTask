<?php

namespace App\Http\Repositories;

use App\Models\Appointment;

class AppointmentRepository
{
    public function getAppointments($service_id, $weekDates)
    {
        return Appointment::select('start_time', 'end_time')
            ->selectRaw('COUNT(start_time) as total')
            ->where('service_id', $service_id)
            ->whereBetween('start_time', [$weekDates[0], $weekDates[count($weekDates) - 1]])
            ->groupBy(['start_time', 'end_time'])
            ->get()
            ->groupBy(function ($appointment) {
                return date('l', strtotime($appointment->start_time)); // Retrieves the day of the week (Monday, Tuesday, etc.)
            })
            ->map(function ($appointments) {
                return $appointments->map(function ($appointment) {
                    return [
                        'start_time' => date('H:i:s', strtotime($appointment->start_time)),
                        'end_time' => date('H:i:s', strtotime($appointment->end_time)),
                        'total' => $appointment->total,
                    ];
                });
            })
            ->toArray();
    }

    public function getConflictingAppointments($service_id, $appointmentStartTime, $appointmentEndTime)
    {
        return Appointment::where('service_id', $service_id)
            ->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
                $query->whereBetween('start_time', [$appointmentStartTime, $appointmentEndTime])
                    ->orWhereBetween('end_time', [$appointmentStartTime, $appointmentEndTime]);
            })->lockForUpdate()->get();
    }

    public function createAppointment($user, $service_id, $appointmentStartTime, $appointmentEndTime)
    {
        $appointment = new Appointment;
        $appointment->service_id = $service_id;
        $appointment->user_id = $user->id;
        $appointment->start_time = $appointmentStartTime;
        $appointment->end_time = $appointmentEndTime;
        $appointment->save();
    }
}
