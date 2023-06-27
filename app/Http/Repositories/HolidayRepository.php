<?php

namespace App\Http\Repositories;

use App\Models\Holiday;

class HolidayRepository
{
    public function getHolidays($service_id, $weekDates)
    {
        return Holiday::selectRaw('DAYNAME(date) as day_name, date, start_time, end_time')
            ->where('service_id', $service_id)
            ->whereIn('date', $weekDates)
            ->get()
            ->keyBy('day_name')
            ->toArray();
    }

    public function getHoliday($service_id, $appointmentStartTime, $appointmentEndTime)
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
