<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapySchedule extends Model
{
    protected $casts = [
        'date' => 'date',
    ];

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }
}
