<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    public function emotionRecords()
    {
        return $this->belongsToMany(EmotionRecord::class, 'emotion_record_question_answer')
            ->withPivot('question_id')
            ->withTimestamps();
    }

    public function emotionRecordQuestions()
    {
        return $this->belongsToMany(Question::class, 'emotion_record_question_answer')
            ->withPivot('emotion_record_id')
            ->withTimestamps();
    }

    public function committedActions()
    {
        return $this->belongsToMany(CommittedAction::class, 'committed_action_question_answer')
            ->withPivot('question_id')
            ->withTimestamps();
    }

    public function committedActionQuestions()
    {
        return $this->belongsToMany(Question::class, 'committed_action_question_answer')
            ->withPivot('committed_action_id')
            ->withTimestamps();
    }
}
