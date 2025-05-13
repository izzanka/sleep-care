<?php

namespace App\Livewire\Records;

use App\Enum\QuestionType;
use App\Service\RecordService;
use App\Service\TherapyService;
use Livewire\Component;

class IdentifyValue extends Component
{
    protected TherapyService $therapyService;

    protected RecordService $recordService;

    public $identifyValue;

    public $labels;

    public function boot(TherapyService $therapyService,
        RecordService $recordService)
    {
        $this->recordService = $recordService;
        $this->therapyService = $therapyService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (! $therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);

            return $this->redirectRoute('doctor.therapies.completed.index');
        }
        $this->identifyValue = $this->recordService->getIdentifyValue($therapyId);
        $this->labels = $this->extractUniqueNotes();
    }

    protected function extractDatasetLabels()
    {
        return $this->identifyValue->questionAnswers
            ->pluck('question.question')
            ->map(fn ($question) => explode(',', $question)[0])
            ->unique()
            ->values()
            ->toArray();
    }

    protected function extractUniqueNotes()
    {
        return $this->identifyValue->questionAnswers
            ->pluck('answer.note')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    protected function extractNumberAnswers()
    {
        return collect($this->identifyValue->questionAnswers)
            ->filter(fn ($qa) => $qa->answer->type === QuestionType::NUMBER->value)
            ->groupBy(fn ($qa) => explode(',', $qa->question->question)[0])
            ->map(fn ($group) => $group->pluck('answer.answer')->map(fn ($val) => (int) $val))
            ->toArray();
    }

    protected function extractTextAnswers()
    {
        return collect($this->identifyValue->questionAnswers)
            ->filter(fn ($qa) => $qa->answer->type === QuestionType::TEXT->value)
            ->groupBy(fn ($qa) => explode(',', $qa->question->question)[0])
            ->map(fn ($group) => $group->pluck('answer.answer'))
            ->toArray();
    }

    public function render()
    {
        return view('livewire.records.identify-value', [
            'datasetLabels' => $this->extractDatasetLabels(),
            'numberAnswers' => $this->extractNumberAnswers(),
            'textAnswers' => $this->extractTextAnswers(),
        ]);
    }
}
