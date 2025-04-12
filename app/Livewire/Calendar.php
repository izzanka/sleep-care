<?php

namespace App\Livewire;

use App\Enum\TherapyStatus;
use App\Models\Therapy;
use App\Models\TherapySchedule;
use Livewire\Component;

class Calendar extends Component
{
    public function render()
    {
        $schedules = [];

        $doctorID = auth()->user()->load('doctor')->doctor->id;
        $therapy = Therapy::where('doctor_id', $doctorID)->where('status', TherapyStatus::IN_PROGRESS->value)->first();
        if ($therapy) {
            $therapySchedules = TherapySchedule::where('therapy_id', $therapy->id)->get();

            foreach ($therapySchedules as $therapySchedule) {
                $title = substr($therapySchedule->title, strpos($therapySchedule->title, 'Sesi'));
                $schedules[] = [
                    'id' => $therapySchedule->id,
                    'title' => $title,
                    'start' => $therapySchedule->date,
                ];
            }
        }

        return view('livewire.calendar', [
            'schedules' => $schedules,
        ]);
    }
}
