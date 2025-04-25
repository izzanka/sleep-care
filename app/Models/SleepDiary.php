<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SleepDiary extends Model
{
    protected $casts = [
        'date' => 'date',
    ];

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(SleepDiaryQuestionAnswer::class)->orderBy('created_at');
    }
}
