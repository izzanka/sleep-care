<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentifyValueQuestionAnswer extends Model
{
    protected $table = 'identify_value_question_answer';

    public function identifyValue()
    {
        return $this->belongsTo(IdentifyValue::class);
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
