<?php

namespace App\Http\Repositories;

use App\Models\ServiceSchedule;

class ServiceScheduleRepository
{
    public function getServiceSchedules($service_id)
    {
        return ServiceSchedule::select('id', 'day_of_week', 'start_time', 'end_time', 'service_id')
            ->with(['service' => function ($query) {
                $query->select('id', 'duration_minutes', 'cleanup_break_minutes', 'max_clients');
            }, 'service.timeBreaks'])
            ->where('service_id', $service_id)
            ->get();
    }

    public function getServiceScheduleByDay($service_id, $dayOfWeek)
    {
        return ServiceSchedule::where('service_id', $service_id)
            ->where('day_of_week', $dayOfWeek)
            ->first();
    }
}