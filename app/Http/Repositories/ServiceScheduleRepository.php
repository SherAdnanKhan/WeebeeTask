<?php

namespace App\Http\Repositories;

use App\Models\ServiceSchedule;

class ServiceScheduleRepository
{
    /**
     * Get service schedules for a given service ID.
     *
     * @param int $service_id - ID of the service
     * @return \Illuminate\Database\Eloquent\Collection - Collection of service schedules
     */
    public function getServiceSchedules(int $service_id): \Illuminate\Database\Eloquent\Collection
    {
        return ServiceSchedule::select('id', 'day_of_week', 'start_time', 'end_time', 'service_id')
            ->with(['service' => function ($query) {
                $query->select('id', 'duration_minutes', 'cleanup_break_minutes', 'max_clients');
            }, 'service.timeBreaks'])
            ->where('service_id', $service_id)
            ->get();
    }

    /**
     * Get service schedule for a given service ID and day of the week.
     *
     * @param int $service_id - ID of the service
     * @param string $dayOfWeek - Day of the week
     * @return \Illuminate\Database\Eloquent\Model|null - Service schedule model or null if not found
     */
    public function getServiceScheduleByDay(int $service_id, string $dayOfWeek): ?\Illuminate\Database\Eloquent\Model
    {
        return ServiceSchedule::where('service_id', $service_id)
            ->where('day_of_week', $dayOfWeek)
            ->first();
    }
}
