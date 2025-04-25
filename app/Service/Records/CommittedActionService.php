<?php

namespace App\Service\Records;

use App\Models\CommittedAction;

class CommittedActionService
{
    public function get(int $therapyId)
    {
        return CommittedAction::where('therapy_id', $therapyId)->first();
    }
}
