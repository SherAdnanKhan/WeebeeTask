<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id', 'appointment_start_time', 'appointment_end_time', 'client_email', 'client_first_name', 'client_last_name'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
