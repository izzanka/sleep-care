<?php

namespace App\Service\Records;

use App\Models\EmotionRecord;

class EmotionRecordService
{
    public function get(int $therapyId)
    {
        return EmotionRecord::with('questionAnswers.question', 'questionAnswers.answer')->where('therapy_id', $therapyId)->first();
    }
}
