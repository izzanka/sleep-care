<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapySchedule extends Model
{
    protected $casts = [
        'date' => 'date',
        'time' => 'datetime',
    ];

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }
}
