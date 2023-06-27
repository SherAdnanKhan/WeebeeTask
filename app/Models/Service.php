<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'name', 'duration_minutes', 'booking_days_in_advance', 'max_clients'
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    public function serviceSchedules()
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    public function timeBreaks()
    {
        return $this->hasMany(TimeBreak::class);
    }
}
