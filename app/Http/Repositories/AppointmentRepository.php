<?php

namespace App\Http\Repositories;

use App\Models\Appointment;
use DateTime;

class AppointmentRepository
{
    /**
     * Get appointments for a given service ID and week dates.
     *
     * @param int $service_id - ID of the service
     * @param array $weekDates - Array of week dates
     * @return array - Array of appointments grouped by day of the week
     */
    public function getAppointments(int $service_id, array $weekDates): array
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

    /**
     * Get conflicting appointments for a given service ID and appointment time range.
     *
     * @param int $service_id - ID of the service
     * @param DateTime $appointmentStartTime - Appointment start time
     * @param DateTime $appointmentEndTime - Appointment end time
     * @return \Illuminate\Database\Eloquent\Collection - Collection of conflicting appointments
     */
    public function getConflictingAppointments(int $service_id, DateTime $appointmentStartTime, DateTime $appointmentEndTime): \Illuminate\Database\Eloquent\Collection
    {
        return Appointment::where('service_id', $service_id)
            ->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
                $query->whereBetween('start_time', [$appointmentStartTime, $appointmentEndTime])
                    ->orWhereBetween('end_time', [$appointmentStartTime, $appointmentEndTime]);
            })->lockForUpdate()->get();
    }

    /**
     * Create a new appointment.
     *
     * @param \Illuminate\Database\Eloquent\Model $user - User model
     * @param int $service_id - ID of the service
     * @param DateTime $appointmentStartTime - Appointment start time
     * @param DateTime $appointmentEndTime - Appointment end time
     * @return void
     */
    public function createAppointment(\Illuminate\Database\Eloquent\Model $user, int $service_id, DateTime $appointmentStartTime, DateTime $appointmentEndTime): void
    {
        $appointment = new Appointment;
        $appointment->service_id = $service_id;
        $appointment->user_id = $user->id;
        $appointment->start_time = $appointmentStartTime;
        $appointment->end_time = $appointmentEndTime;
        $appointment->save();
    }
}
