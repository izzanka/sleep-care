<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThoughtRecordQuestionAnswer extends Model
{
    public $timestamps = false;
    protected $table = 'thought_record_question_answer';

    public function thoughtRecord()
    {
        return $this->belongsTo(ThoughtRecord::class);
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
