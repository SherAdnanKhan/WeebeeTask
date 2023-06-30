<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'duration_minutes', 'booking_days_in_advance', 'max_clients'
    ];

    /**
     * Get the appointments associated with the service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the holidays associated with the service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    /**
     * Get the service schedules associated with the service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    /**
     * Get the time breaks associated with the service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeBreaks(): HasMany
    {
        return $this->hasMany(TimeBreak::class);
    }
}

