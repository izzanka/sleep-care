<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
//    public function sleepDiaries()
//    {
//        return $this->belongsToMany(SleepDiary::class, 'sleep_diary_question_answer')
//            ->withPivot('question_id')
//            ->withTimestamps();
//    }
//
//    public function sleepDiaryQuestions()
//    {
//        return $this->belongsToMany(Question::class, 'sleep_diary_question_answer')
//            ->withPivot('sleep_diary_id')
//            ->withTimestamps();
//    }

    public function identifyValues()
    {
        return $this->belongsToMany(IdentifyValue::class, 'identify_value_question_answer')
            ->withPivot('question_id')
            ->withTimestamps();
    }

    public function identifyValueQuestions()
    {
        return $this->belongsToMany(Question::class, 'identify_value_question_answer')
            ->withPivot('identify_value_id')
            ->withTimestamps();
    }

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

    public function thoughtRecords()
    {
        return $this->belongsToMany(ThoughtRecord::class, 'thought_record_question_answer')
            ->withPivot('question_id')
            ->withTimestamps();
    }

    public function thoughtRecordQuestions()
    {
        return $this->belongsToMany(Question::class, 'thought_record_question_answer')
            ->withPivot('thought_record_id')
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
