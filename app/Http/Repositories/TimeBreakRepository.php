<?php

namespace App\Http\Repositories;

use App\Models\TimeBreak;

class TimeBreakRepository
{
    /**
     * Get time breaks for a service.
     *
     * @param int $service_id - ID of the service
     * @return \Illuminate\Database\Eloquent\Collection - Collection of time breaks
     */
    public function getTimeBreaks(int $service_id): \Illuminate\Database\Eloquent\Collection
    {
        return TimeBreak::where('service_id', $service_id)->get();
    }
}
