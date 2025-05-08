<?php

namespace App\Service\Records;

use App\Models\ThoughtRecord;

class ThoughtRecordService
{
    public function get(int $therapyId)
    {
        return ThoughtRecord::with('questionAnswers.question', 'questionAnswers.answer')->where('therapy_id', $therapyId)->first();
    }
}
