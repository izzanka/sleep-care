<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SleepDiaryQuestionAnswer extends Model
{
    public $timestamps = false;

    protected $table = 'sleep_diary_question_answer';

    public function sleepDiary()
    {
        return $this->belongsTo(SleepDiary::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
