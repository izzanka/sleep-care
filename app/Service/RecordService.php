<?php

namespace App\Service;

use App\Models\CommittedAction;
use App\Models\EmotionRecord;
use App\Models\IdentifyValue;
use App\Models\SleepDiary;
use App\Models\ThoughtRecord;

class RecordService
{
    public function getCommittedActions(int $therapyId)
    {
        return CommittedAction::with('questionAnswers.question', 'questionAnswers.answer')->where('therapy_id', $therapyId)->first();
    }

    public function generateCommittedAction(int $therapyId)
    {
        return CommittedAction::create(['therapy_id' => $therapyId]);
    }

    public function getEmotionRecords(int $therapyId)
    {
        return EmotionRecord::with('questionAnswers.question', 'questionAnswers.answer')->where('therapy_id', $therapyId)->first();
    }

    public function generateEmotionRecord(int $therapyId)
    {
        return EmotionRecord::create(['therapy_id' => $therapyId]);
    }

    public function getIdentifyValues(int $therapyId)
    {
        return IdentifyValue::with('questionAnswers.question', 'questionAnswers.answer')->where('therapy_id', $therapyId)->first();
    }

    public function generateIdentifyValue(int $therapyId)
    {
        return IdentifyValue::create(['therapy_id' => $therapyId]);
    }

    public function getSleepDiaries(int $therapyId)
    {
        return SleepDiary::where('therapy_id', $therapyId)
            ->orderBy('week')
            ->orderBy('day')
            ->get()
            ->groupBy('week');
    }

    public function generateSleepDiaries(int $therapyId, $therapyStartDate)
    {
        for ($week = 1; $week <= 6; $week++) {
            for ($day = 1; $day <= 7; $day++) {
                $currentDate = $therapyStartDate->addDays((($week - 1) * 7) + ($day - 1));

                SleepDiary::create([
                    'therapy_id' => $therapyId,
                    'week' => $week,
                    'day' => $day,
                    'date' => $currentDate->toDateString(),
                    'title' => 'Sleep Diary Minggu '.$week,
                ]);
            }
        }
    }

    public function getSleepDiaryByID(int $sleepDiaryId, int $therapyId)
    {
        return SleepDiary::with('questionAnswers.question', 'questionAnswers.answer')->where('id', $sleepDiaryId)->where('therapy_id', $therapyId);
    }

    public function getThoughtRecords(int $therapyId)
    {
        return ThoughtRecord::with('questionAnswers.question', 'questionAnswers.answer')->where('therapy_id', $therapyId)->first();
    }

    public function generateThoughtRecord(int $therapyId)
    {
        return ThoughtRecord::create(['therapy_id' => $therapyId]);
    }
}
