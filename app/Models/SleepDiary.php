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

    //    public function questions()
    //    {
    //        return $this->belongsToMany(Question::class, 'sleep_diary_question_answer')
    //            ->withPivot('answer_id')
    //            ->withTimestamps();
    //    }
    //
    //    public function answers()
    //    {
    //        return $this->belongsToMany(Answer::class, 'sleep_diary_question_answer')
    //            ->withPivot('question_id')
    //            ->withTimestamps();
    //    }

    public function questionAnswers()
    {
        return $this->hasMany(SleepDiaryQuestionAnswer::class);
    }

    public function getDayAndMonthAttribute()
    {
        $date = Carbon::parse($this->date);

        return $date->format('d/m');
    }
}
