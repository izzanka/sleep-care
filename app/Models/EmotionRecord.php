<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmotionRecord extends Model
{
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'emotion_record_question_answer')
            ->withPivot('answer_id')
            ->withTimestamps();
    }

    public function answers()
    {
        return $this->belongsToMany(Answer::class, 'emotion_record_question_answer')
            ->withPivot('question_id')
            ->withTimestamps();
    }
}
