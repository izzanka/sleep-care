<?php

namespace App\Service\Records;

use App\Models\ThoughtRecord;

class ThoughtRecordService
{
    public function get(int $therapyId)
    {
        return ThoughtRecord::where('therapy_id', $therapyId)->first();
    }
}
