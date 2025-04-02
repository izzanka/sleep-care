<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapySchedule extends Model
{
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }
}
