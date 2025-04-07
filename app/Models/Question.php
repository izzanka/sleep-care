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
