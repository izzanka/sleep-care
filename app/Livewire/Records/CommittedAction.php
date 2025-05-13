<?php

namespace App\Livewire\Records;

use App\Service\RecordService;
use App\Service\TherapyService;
use Livewire\Component;

class CommittedAction extends Component
{
    protected TherapyService $therapyService;

    protected RecordService $recordService;

    public $committedAction;

    public function boot(TherapyService $therapyService, RecordService $recordService)
    {
        $this->therapyService = $therapyService;
        $this->recordService = $recordService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (! $therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);

            return $this->redirectRoute('doctor.therapies.completed.index');
        }
        $this->committedAction = $this->recordService->getCommittedAction($therapyId);
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
