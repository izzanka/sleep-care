<?php

namespace App\Service\Records;

use App\Models\SleepDiary;

class SleepDiaryService
{
    public function get(int $therapyId)
    {
        return SleepDiary::where('therapy_id', $therapyId)
            ->orderBy('week')
            ->orderBy('day')
            ->get()
            ->groupBy('week');
    }

    public function find(int $sleepDiaryId)
    {
        return SleepDiary::with('questionAnswers.question', 'questionAnswers.answer')->find($sleepDiaryId);
    }
}
