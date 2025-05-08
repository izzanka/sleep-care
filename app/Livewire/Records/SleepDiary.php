<?php

namespace App\Livewire\Records;

use App\Service\ChartService;
use App\Service\Records\SleepDiaryService;
use App\Service\TherapyService;
use Livewire\Component;

class SleepDiary extends Component
{
    protected ChartService $chartService;

    protected SleepDiaryService $sleepDiaryService;

    protected TherapyService $therapyService;

    public $therapy;

    public $dropdownLabels;

    public function boot(ChartService $chartService,
        SleepDiaryService $sleepDiaryService,
        TherapyService $therapyService)
    {
        $this->chartService = $chartService;
        $this->sleepDiaryService = $sleepDiaryService;
        $this->therapyService = $therapyService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (! $this->therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);

            return $this->redirectRoute('doctor.therapies.completed.index');
        }
        $this->dropdownLabels = $this->chartService->labeling($this->therapy->start_date);
    }

    protected function getQuestions($sleepDiaries)
    {
        $questions = $sleepDiaries
            ->flatten(1)
            ->pluck('questionAnswers')
            ->flatten()
            ->pluck('question')
            ->unique('id')
            ->values();

        return $questions
            ->filter(fn ($q) => is_null($q->parent_id))
            ->map(function ($parent) use ($questions) {
                $parent->children = $questions->where('parent_id', $parent->id)->values();

                return $parent;
            })
            ->values();
    }

    public function render()
    {
        $sleepDiaries = $this->sleepDiaryService->get($this->therapy->id);
        $structuredQuestions = $this->getQuestions($sleepDiaries);

        return view('livewire.records.sleep-diary', [
            'sleepDiaries' => $sleepDiaries,
            'structuredQuestions' => $structuredQuestions,
        ]);
    }
}
