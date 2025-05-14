<?php

namespace App\Service;

use App\Models\CommittedAction;
use App\Models\EmotionRecord;
use App\Models\IdentifyValue;
use App\Models\SleepDiary;
use App\Models\ThoughtRecord;

class RecordService
{
    public function getCommittedAction(?int $therapyId = null, ?int $committedActionId = null)
    {
        $query = CommittedAction::query();
        $query->with('questionAnswers.question', 'questionAnswers.answer');

        if ($therapyId) {
            $query->where('therapy_id', $therapyId);
        }

        if ($committedActionId) {
            $query->where('id', $committedActionId);
        }

        return $query->first();
    }

    public function generateCommittedAction(int $therapyId)
    {
        return CommittedAction::create(['therapy_id' => $therapyId]);
    }

    public function getEmotionRecord(?int $therapyId = null, ?int $emotionRecordId = null)
    {
        $query = EmotionRecord::query();
        $query->with('questionAnswers.question', 'questionAnswers.answer');

        if ($therapyId) {
            $query->where('therapy_id', $therapyId);
        }

        if ($emotionRecordId) {
            $query->where('id', $emotionRecordId);
        }

        return $query->first();
    }

    public function generateEmotionRecord(int $therapyId)
    {
        return EmotionRecord::create(['therapy_id' => $therapyId]);
    }

    public function getIdentifyValue(?int $therapyId = null, ?int $identifyValueId = null)
    {
        $query = IdentifyValue::query();
        $query->with('questionAnswers.question', 'questionAnswers.answer');

        if ($therapyId) {
            $query->where('therapy_id', $therapyId);
        }

        if ($identifyValueId) {
            $query->where('id', $identifyValueId);
        }

        return $query->first();
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

    public function getSleepDiaryByID(int $therapyId, int $sleepDiaryId)
    {
        return SleepDiary::with('questionAnswers.question', 'questionAnswers.answer')->where('id', $sleepDiaryId)->where('therapy_id', $therapyId)->first();
    }

    public function getThoughtRecord(?int $therapyId = null, ?int $thoughtRecordId = null)
    {
        $query = ThoughtRecord::query();
        $query->with('questionAnswers.question', 'questionAnswers.answer');

        if ($therapyId) {
            $query->where('therapy_id', $therapyId);
        }

        if ($thoughtRecordId) {
            $query->where('id', $thoughtRecordId);
        }

        return $query->first();
    }

    public function generateThoughtRecord(int $therapyId)
    {
        return ThoughtRecord::create(['therapy_id' => $therapyId]);
    }
}
