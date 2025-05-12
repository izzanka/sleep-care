<?php

namespace App\Livewire\Records;

use App\Service\RecordService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Livewire\Component;

class EmotionRecord extends Component
{
    protected TherapyService $therapyService;

    protected RecordService $recordService;

    public $emotionRecord;

    public function boot(TherapyService $therapyService, RecordService $recordService)
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
        $this->emotionRecord = $this->recordService->getEmotionRecords($therapyId);
    }

    public function render()
    {
        $questions = $this->emotionRecord->questionAnswers
            ->pluck('question.question')
            ->unique()
            ->values();

        $chunks = $this->emotionRecord->questionAnswers->chunk(count($questions))
            ->sortByDesc(fn ($chunk) => Carbon::parse($chunk->keyBy(fn ($qa) => $qa->question_id)[27]->answer->answer))
            ->values();

        return view('livewire.records.emotion-record', [
            'questions' => $questions,
            'answerRows' => $chunks,
        ]);
    }
}
