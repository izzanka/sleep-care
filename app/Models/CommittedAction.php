<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommittedAction extends Model
{
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'committed_action_question_answer')
            ->withPivot('answer_id')
            ->withTimestamps();
    }

    public function answers()
    {
        return $this->belongsToMany(Answer::class, 'committed_action_question_answer')
            ->withPivot('question_id')
            ->withTimestamps();
    }
}
