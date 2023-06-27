<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'date', 'start_time', 'end_time'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
