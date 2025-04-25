<?php

namespace App\Service;

use App\Models\TherapySchedule;

class TherapyScheduleService
{
    public function get(int $therapyId)
    {
        return TherapySchedule::where('therapy_id', $therapyId)->get();
    }

    public function find(int $scheduleId)
    {
        return TherapySchedule::find($scheduleId);
    }
}
