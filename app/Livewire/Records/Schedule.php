<?php

namespace App\Livewire\Records;

use App\Service\TherapyScheduleService;
use App\Service\TherapyService;
use Livewire\Component;

class Schedule extends Component
{
    protected TherapyService $therapyService;

    protected TherapyScheduleService $therapyScheduleService;

    public $therapySchedules;

    public function boot(TherapyService $therapyService, TherapyScheduleService $therapyScheduleService)
    {
        $this->therapyService = $therapyService;
        $this->therapyScheduleService = $therapyScheduleService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (! $therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);

            return $this->redirectRoute('doctor.therapies.completed.index');
        }
        $this->therapySchedules = $this->therapyScheduleService->get($therapyId);
    }

    public function render()
    {
        return view('livewire.records.schedule');
    }
}
