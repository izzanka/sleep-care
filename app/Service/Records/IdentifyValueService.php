<?php

namespace App\Service\Records;

use App\Models\IdentifyValue;

class IdentifyValueService
{
    public function get(int $therapyId)
    {
        return IdentifyValue::with('questionAnswers.question', 'questionAnswers.answer')->where('therapy_id', $therapyId)->first();
    }
}
