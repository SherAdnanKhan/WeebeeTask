<?php

namespace App\Http\Repositories;

use App\Models\Service;

class ServiceRepository
{
    /**
     * Get a service by ID.
     *
     * @param int $service_id - ID of the service
     * @return \Illuminate\Database\Eloquent\Model|null - Service model or null if not found
     */
    public function getService(int $service_id): ?\Illuminate\Database\Eloquent\Model
    {
        return Service::find($service_id);
    }
}
