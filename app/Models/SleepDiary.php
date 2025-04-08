<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SleepDiary extends Model
{
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(SleepDiaryQuestionAnswer::class)->orderBy('created_at');
    }

    public function getDayAndMonthAttribute()
    {
        $date = Carbon::parse($this->date);

        return $date->format('d/m');
    }
}
