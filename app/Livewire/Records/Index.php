<?php

namespace App\Livewire\Records;

use App\Enum\Problem;
use App\Enum\TherapyStatus;
use App\Service\TherapyService;
use Livewire\Component;

class Index extends Component
{
    protected TherapyService $therapyService;

    public $therapy;
    public $problems;

    public function boot(TherapyService $therapyService)
    {
        $this->therapyService = $therapyService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->find(doctorId: $doctorId, id: $therapyId)[0];
        if (! $this->therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);
            return $this->redirectRoute('doctor.therapies.completed.index');
        }
        $this->problems = $this->formatPatientProblems($this->therapy->patient->problems);
    }

    protected function formatPatientProblems(?string $problems)
    {
        if (!$problems) {
            return '-';
        }

        return collect(json_decode($problems))
            ->map(fn($problem) => Problem::tryFrom($problem)?->label() ?? $problem)
            ->implode(', ');
    }
    public function render()
    {
        return view('livewire.records.index');
    }
}
