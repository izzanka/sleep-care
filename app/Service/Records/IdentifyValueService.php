<?php

namespace App\Service\Records;

use App\Models\IdentifyValue;

class IdentifyValueService
{
    public function get(int $therapyId)
    {
        return IdentifyValue::where('therapy_id', $therapyId)->first();
    }
}
