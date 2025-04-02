<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentifyValue extends Model
{
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'identify_value_question_answer')
            ->withPivot('answer_id')
            ->withTimestamps();
    }

    public function answers()
    {
        return $this->belongsToMany(Answer::class, 'identify_value_question_answer')
            ->withPivot('question_id')
            ->withTimestamps();
    }
}
