<?php

namespace App\Livewire\Records;

use App\Service\RecordService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Livewire\Component;

class ThoughtRecord extends Component
{
    protected TherapyService $therapyService;

    protected RecordService $recordService;

    public $thoughtRecords;

    public function boot(TherapyService $therapyService,
        RecordService $recordService)
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
        $this->thoughtRecords = $this->recordService->getThoughtRecords($therapyId);
    }

    protected function extractQuestions()
    {
        return $this->thoughtRecords->questionAnswers
            ->pluck('question.question')
            ->unique()
            ->values();
    }

    public function render()
    {
        $questions = $this->extractQuestions();
        $chunks = $this->thoughtRecords->questionAnswers->chunk(count($questions))->sortByDesc(function ($chunk) {
            $dateAnswer = $chunk->keyBy(fn ($qa) => $qa->question_id)[23]->answer->answer;

            return Carbon::parse($dateAnswer);
        })->values();

        return view('livewire.records.thought-record', [
            'thoughtRecordQuestions' => $questions,
            'chunks' => $chunks,
        ]);
    }
}
