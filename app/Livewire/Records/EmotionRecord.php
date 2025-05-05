<?php

namespace App\Livewire\Records;

use App\Service\Records\EmotionRecordService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Livewire\Component;

class EmotionRecord extends Component
{
    protected TherapyService $therapyService;

    protected EmotionRecordService $emotionRecordService;

    public $emotionRecord;

    public function boot(TherapyService $therapyService, EmotionRecordService $emotionRecordService)
    {
        $this->emotionRecordService = $emotionRecordService;
        $this->therapyService = $therapyService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $therapy = $this->therapyService->find(doctorId: $doctorId, id: $therapyId)[0];
        if (! $therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);
            return $this->redirectRoute('doctor.therapies.completed.index');
        }
        $this->emotionRecord = $this->emotionRecordService->get($therapyId);
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
