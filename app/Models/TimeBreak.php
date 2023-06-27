<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeBreak extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'break_start', 'break_end'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
