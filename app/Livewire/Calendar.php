<?php

namespace App\Livewire;

use App\Enum\TherapyStatus;
use App\Models\Therapy;
use App\Models\TherapySchedule;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;

class Calendar extends Component
{
    protected function extractTitle(string $title, string $name): string
    {
        $fullTitle = Str::of($title)->contains('Sesi')
            ? Str::of($title)->after('Sesi')->prepend('Sesi')
            : $title;

        return $fullTitle.' ('.$name.')';
    }

    public function render()
    {
        $schedules = [];

        if (auth()->user()->is_therapy_in_progress) {
            $therapies = Therapy::where('doctor_id', auth()->user()->doctor->id)
                ->where('status', TherapyStatus::IN_PROGRESS->value)
                ->latest()
                ->get();

            foreach ($therapies as $therapy) {
                $therapySchedules = TherapySchedule::where('therapy_id', $therapy->id)
                    ->whereNotNull('date')
                    ->get();

                foreach ($therapySchedules as $schedule) {
                    $time = Carbon::parse($schedule->time);
                    $schedules[] = [
                        'id' => $schedule->therapy_id,
                        'title' => $time->format('H:i').' - '.$this->extractTitle($schedule->title, $therapy->patient->name),
                        'start' => $schedule->date->toDateString(),
                    ];
                }
            }
        }

        return view('livewire.calendar', [
            'schedules' => $schedules,
        ]);
    }
}
