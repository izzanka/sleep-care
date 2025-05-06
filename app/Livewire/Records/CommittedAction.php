<?php

namespace App\Livewire\Records;

use App\Service\Records\CommittedActionService;
use App\Service\TherapyService;
use Livewire\Component;

class CommittedAction extends Component
{
    protected TherapyService $therapyService;

    protected CommittedActionService $committedActionService;

    public $committedAction;

    public function boot(TherapyService $therapyService, CommittedActionService $committedActionService)
    {
        $this->therapyService = $therapyService;
        $this->committedActionService = $committedActionService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (! $therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);
            return $this->redirectRoute('doctor.therapies.completed.index');
        }
        $this->committedAction = $this->committedActionService->get($therapyId);
    }

    public function render()
    {
        $questionAnswers = $this->committedAction->questionAnswers;
        $questionLabels = $questionAnswers->pluck('question.question')->unique()->values();
        $tableRows = $questionAnswers->sortByDesc('answer.created_at')->chunk($questionLabels->count());

        return view('livewire.records.committed-action', [
            'questions' => $questionLabels,
            'rows' => $tableRows,
        ]);
    }
}
