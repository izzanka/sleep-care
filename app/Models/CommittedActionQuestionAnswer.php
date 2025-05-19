<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommittedActionQuestionAnswer extends Model
{
    public $timestamps = false;
    protected $table = 'committed_action_question_answer';

    public function committedAction()
    {
        return $this->belongsTo(CommittedAction::class);
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
