<?php

namespace App\Http\Repositories;

use App\Models\Holiday;
use DateTime;

class HolidayRepository
{
    /**
     * Get holidays for a given service ID and week dates.
     *
     * @param int $service_id - ID of the service
     * @param array $weekDates - Array of week dates
     * @return array - Array of holidays keyed by day name
     */
    public function getHolidays(int $service_id, array $weekDates): array
    {
        return Holiday::selectRaw('DAYNAME(date) as day_name, date, start_time, end_time')
            ->where('service_id', $service_id)
            ->whereIn('date', $weekDates)
            ->get()
            ->keyBy('day_name')
            ->toArray();
    }

    /**
     * Get holiday for a given service ID and appointment time range.
     *
     * @param int $service_id - ID of the service
     * @param DateTime $appointmentStartTime - Appointment start time
     * @param DateTime $appointmentEndTime - Appointment end time
     * @return \Illuminate\Database\Eloquent\Model|null - Holiday model or null if not found
     */
    public function getHoliday(int $service_id, DateTime $appointmentStartTime, DateTime $appointmentEndTime): ?\Illuminate\Database\Eloquent\Model
    {
        $holiday = Holiday::where('service_id', $service_id)
            ->where('date', $appointmentStartTime->format('Y-m-d'))
            ->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
                $query->where(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
                    $query->whereNull('start_time')->whereNull('end_time')
                        ->orWhere(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
                            $query->where(function ($query) use ($appointmentStartTime) {
                                $query->where('start_time', '<=', $appointmentStartTime->format('H:i:s'))
                                    ->where('end_time', '>=', $appointmentStartTime->format('H:i:s'));
                            })->orWhere(function ($query) use ($appointmentEndTime) {
                                $query->where('start_time', '<=', $appointmentEndTime->format('H:i:s'))
                                    ->where('end_time', '>=', $appointmentEndTime->format('H:i:s'));
                            })->orWhere(function ($query) use ($appointmentStartTime, $appointmentEndTime) {
                                $query->where('start_time', '>=', $appointmentStartTime->format('H:i:s'))
                                    ->where('end_time', '<=', $appointmentEndTime->format('H:i:s'));
                            });
                        });
                });
            })
            ->first();

        return $holiday;
    }
}
