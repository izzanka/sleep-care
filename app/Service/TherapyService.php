<?php

namespace App\Service;

use App\Enum\TherapyStatus;
use App\Models\Therapy;

class TherapyService
{
    public function getInProgress(int $doctorId)
    {
        return Therapy::where('doctor_id', $doctorId)
            ->where('status', TherapyStatus::IN_PROGRESS->value)->latest()->first();
    }
}
