<?php

namespace App\Http\Repositories;

use App\Models\Service;

class ServiceRepository
{
    public function getService($service_id)
    {
        return Service::find($service_id);
    }
}