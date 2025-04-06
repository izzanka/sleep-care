<?php

namespace App\Models;

use App\Enum\QuestionType;
use App\Enum\RecordType;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Question extends Model
{
    use Searchable;

    protected function casts(): array
    {
        return [
            'record_type' => RecordType::class,
            'type' => QuestionType::class,
        ];
    }

//    public function sleepDiaries()
//    {
//        return $this->belongsToMany(SleepDiary::class, 'sleep_diary_question_answer')
//            ->withPivot('answer_id')
//            ->withTimestamps();
//    }
//
//    public function sleepDiaryAnswers()
//    {
//        return $this->belongsToMany(Answer::class, 'sleep_diary_question_answer')
//            ->withPivot('sleep_diary_id')
//            ->withTimestamps();
//    }

    public function identifyValues()
    {
        return $this->belongsToMany(IdentifyValue::class, 'identify_value_question_answer')
            ->withPivot('answer_id')
            ->withTimestamps();
    }

    public function identifyValueAnswers()
    {
        return $this->belongsToMany(Answer::class, 'identify_value_question_answer')
            ->withPivot('identify_value_id')
            ->withTimestamps();
    }

    public function emotionRecords()
    {
        return $this->belongsToMany(EmotionRecord::class, 'emotion_record_question_answer')
            ->withPivot('answer_id')
            ->withTimestamps();
    }

    public function emotionRecordAnswers()
    {
        return $this->belongsToMany(Answer::class, 'emotion_record_question_answer')
            ->withPivot('emotion_record_id')
            ->withTimestamps();
    }

    public function thoughtRecord()
    {
        return $this->belongsToMany(ThoughtRecord::class, 'thought_record_question_answer')
            ->withPivot('answer_id')
            ->withTimestamps();
    }

    public function thoughtRecordAnswers()
    {
        return $this->belongsToMany(Answer::class, 'thought_record_question_answer')
            ->withPivot('thought_record_id')
            ->withTimestamps();
    }

    public function committedActions()
    {
        return $this->belongsToMany(CommittedAction::class, 'committed_action_question_answer')
            ->withPivot('answer_id')
            ->withTimestamps();
    }

    public function committedAnswers()
    {
        return $this->belongsToMany(Answer::class, 'committed_question_answer')
            ->withPivot('committed_action_id')
            ->withTimestamps();
    }

    #[SearchUsingFullText(['question'])]
    public function toSearchableArray()
    {
        return [
            'question' => $this->question,
        ];
    }
}
