<?php

namespace App\Livewire;

use App\Enum\TherapyStatus;
use App\Models\Therapy;
use App\Models\TherapySchedule;
use Illuminate\Support\Str;
use Livewire\Component;

class Calendar extends Component
{
    protected function extractTitle(string $title): string
    {
        return Str::of($title)->contains('Sesi')
            ? Str::of($title)->after('Sesi')->prepend('Sesi')
            : $title;
    }

    public function render()
    {
        $schedules = [];

        $doctorID = auth()->user()->doctor->id;

        $therapy = Therapy::select('id', 'doctor_id', 'status')->where([
            ['doctor_id', $doctorID],
            ['status', TherapyStatus::IN_PROGRESS->value],
        ])->first();

        if ($therapy) {
            $therapySchedules = TherapySchedule::where('therapy_id', $therapy->id)->whereNotNull('date')->get();

            $schedules = $therapySchedules->map(function ($schedule) {
                return [
                    'id' => $schedule->therapy_id,
                    'title' => $this->extractTitle($schedule->title),
                    'start' => $schedule->date->toDateString(),
                ];
            })->toArray();
        }

        return view('livewire.calendar', [
            'schedules' => $schedules,
        ]);
    }
}
