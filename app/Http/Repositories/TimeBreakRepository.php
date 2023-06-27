<?php

namespace App\Http\Repositories;

use App\Models\TimeBreak;

class TimeBreakRepository
{
    public function getTimeBreaks($service_id)
    {
        return TimeBreak::where('service_id', $service_id)->get();
    }
}