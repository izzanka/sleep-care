<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmotionRecordQuestionAnswer extends Model
{
    public $timestamps = false;

    protected $table = 'emotion_record_question_answer';

    public function emotionRecord()
    {
        return $this->belongsTo(EmotionRecord::class);
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
