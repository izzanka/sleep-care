<?php

namespace App\Http\Controllers\Api;

use App\Enum\QuestionType;
use App\Enum\RecordType;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Answer;
use App\Service\AnswerService;
use App\Service\QuestionService;
use App\Service\RecordService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class RecordController extends Controller
{
    public function __construct(protected RecordService $recordService,
        protected TherapyService $therapyService,
        protected QuestionService $questionService,
        protected AnswerService $answerService) {}

    private function getRecordByType(?string $recordType = null, ?int $therapyId = null, ?int $id = null)
    {
        return match ($recordType) {
            RecordType::COMMITTED_ACTION->value => $this->recordService->getCommittedAction($therapyId, $id),
            RecordType::EMOTION_RECORD->value => $this->recordService->getEmotionRecord($therapyId, $id),
            RecordType::IDENTIFY_VALUE->value => $this->recordService->getIdentifyValue($therapyId, $id),
            RecordType::THOUGHT_RECORD->value => $this->recordService->getThoughtRecord($therapyId, $id),
            RecordType::SLEEP_DIARY->value => $this->recordService->getSleepDiaryByID($therapyId, $id),
            default => null,
        };
    }

    public function get(Request $request)
    {
        $validated = $request->validate([
            'therapy_id' => ['required', 'int'],
            'record_type' => ['required', new Enum(RecordType::class)],
            'week' => ['nullable', 'int', 'min:1', 'max:6'],
        ]);

        try {
            $therapy = $this->therapyService
                ->get(patientId: auth()->id(), id: $validated['therapy_id'])
                ->first();

            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            if ($validated['record_type'] === RecordType::SLEEP_DIARY->value) {
                return Response::error("Data {$validated['record_type']} tidak ditemukan.", 404);
            }

            $record = $this->getRecordByType($validated['record_type'], $therapy->id);
            if (! $record) {
                return Response::error("Data {$validated['record_type']} tidak ditemukan.", 404);
            }

            $questions = collect($record->questionAnswers)
                ->pluck('question')
                ->unique('id')
                ->values();

            if (isset($validated['week'])) {
                $startDate = $therapy->start_date;
                $week = (int) $validated['week'];

                $weekStart = $startDate->addWeeks($week - 1)->startOfDay();
                $weekEnd = $startDate->addWeeks($week)->subDay()->endOfDay();

                $rawAnswers = collect($record->questionAnswers);

                $sessions = $rawAnswers->groupBy(function ($qa) {
                    return $qa->answer->created_at;
                });

                $filteredAnswers = $sessions->flatMap(function ($group) use ($weekStart, $weekEnd) {
                    $dateAnswer = $group->firstWhere(fn ($qa) => $qa->answer->type === QuestionType::DATE->value);

                    $usedAt = $dateAnswer
                        ? Carbon::parse($dateAnswer->answer->answer)
                        : $group->first()?->answer->created_at;

                    if (! ($usedAt >= $weekStart && $usedAt <= $weekEnd)) {
                        return [];
                    }

                    return $group->map(function ($qa) {
                        return [
                            'question_id' => $qa->question_id,
                            'answer' => $qa->answer,
                            'comment' => $qa->comment,
                        ];
                    });
                })->values();

                return Response::success([
                    'id' => $record->id,
                    'therapy_id' => $therapy->id,
                    'week' => $week,
                    'questions' => QuestionResource::collection($questions),
                    'answers' => $filteredAnswers,
                ], "Berhasil mendapatkan data jawaban {$validated['record_type']} untuk minggu ke-{$week}.");

            } else {

                $answers = collect($record->questionAnswers)->map(fn ($qa) => [
                    'question_id' => $qa->question_id,
                    'answer' => $qa->answer,
                    'comment' => $qa->comment,
                ]);

                return Response::success([
                    'id' => $record->id,
                    'therapy_id' => $therapy->id,
                    'questions' => QuestionResource::collection($questions),
                    'answers' => $answers,
                ], "Berhasil mendapatkan data {$validated['record_type']}.");
            }

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getIdentifyValueArea()
    {
        $areas = [
            'Keluarga', 'Pernikahan/Relasi', 'Pertemanan', 'Pekerjaan/Karir', 'Pendidikan/Pengembangan Diri',
            'Rekreasi/Hiburan/Waktu Luang', 'Spiritualitas', 'Komunitas/Relawan', 'Lingkungan/Alam', 'Kesehatan',
        ];

        return Response::success($areas, 'Berhasil mendapatkan data area identify_value.');
    }

    public function getEmotionRecordEmotion()
    {
        $emotions = ['Bahagia', 'Sedih', 'Marah', 'Takut', 'Jijik', 'Terkejut'];

        return Response::success($emotions, 'Berhasil mendapatkan daftar emosi emotion_record.');
    }

    public function getSleepDiaries(Request $request)
    {
        $validated = $request->validate([
            'therapy_id' => ['required', 'int'],
        ]);

        try {

            $therapy = $this->therapyService->get(patientId: auth()->id(), id: $validated['therapy_id'])->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $sleepDiaries = $this->recordService->getSleepDiaries($therapy->id);
            if (! $sleepDiaries) {
                return Response::error('Data sleep_diary tidak ditemukan.', 404);
            }

            return Response::success([
                'sleep_diaries' => $sleepDiaries,
            ], 'Berhasil mendapatkan data sleep_diary.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getSleepDiaryByID(Request $request, int $id)
    {
        $validated = $request->validate([
            'therapy_id' => ['required', 'int'],
        ]);

        try {

            $therapy = $this->therapyService->get(patientId: auth()->id(), id: $validated['therapy_id'])->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $sleepDiary = $this->recordService->getSleepDiaryByID($validated['therapy_id'], $id);
            if (! $sleepDiary) {
                return Response::error('Data sleep_diary tidak ditemukan.', 404);
            }

            $questions = collect($sleepDiary->questionAnswers)
                ->pluck('question')
                ->unique('id')
                ->values();

            $answers = collect($sleepDiary->questionAnswers)->map(fn ($qa) => [
                'question_id' => $qa->question_id,
                'answer' => $qa->answer,
            ]);

            return Response::success([
                'id' => $sleepDiary->id,
                'therapy_id' => $therapy->id,
                'questions' => QuestionResource::collection($questions),
                'answers' => $answers,
            ], 'Berhasil mendapatkan data detail sleep_diary.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'therapy_id' => ['required', 'int'],
            'record_type' => ['required', new Enum(RecordType::class)],
            'record_id' => ['required', 'int'],
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'int'],
            'answers.*.type' => ['required', new Enum(QuestionType::class)],
            'answers.*.answer' => ['required'],
            'answers.*.note' => ['nullable', 'string', 'max:225'],
        ]);

        try {
            $therapy = $this->therapyService->get(
                patientId: auth()->id(), id: $validated['therapy_id']
            )->first();

            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            if (! now()->between($therapy->start_date, $therapy->end_date)) {
                return Response::error('Tanggal tidak valid.', 400);
            }

            $record = $this->getRecordByType($validated['record_type'], $therapy->id, $validated['record_id']);
            if (! $record) {
                return Response::error("Data {$validated['record_type']} tidak ditemukan.", 404);
            }

            $table = $validated['record_type'].'_question_answer';

            DB::beginTransaction();

            foreach ($validated['answers'] as $answerData) {
                $question = $this->questionService->get($validated['record_type'], $answerData['question_id'])->first();
                if (! $question) {
                    DB::rollBack();

                    return Response::error("Pertanyaan tidak ditemukan (ID: {$answerData['question_id']}).", 404);
                }

                if ($question->type->value != $answerData['type']) {
                    DB::rollBack();

                    return Response::error("Tipe jawaban tidak sesuai untuk pertanyaan ID: {$question->id}", 422);
                }

                $answer = Answer::create([
                    'type' => $answerData['type'],
                    'answer' => $answerData['answer'],
                    'note' => $answerData['note'],
                ]);

                DB::table($table)->insert([
                    $validated['record_type'].'_id' => $record->id,
                    'question_id' => $question->id,
                    'answer_id' => $answer->id,
                ]);
            }

            DB::commit();

            return Response::success(null, 'Jawaban berhasil disimpan.');

        } catch (\Exception $exception) {
            DB::rollBack();

            return Response::error($exception->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'therapy_id' => ['required', 'int'],
            'record_type' => ['required', new Enum(RecordType::class)],
            'record_id' => ['required', 'int'],
            'answers' => ['required', 'array'],
            'answers.*.id' => ['required', 'int'],
            'answers.*.question_id' => ['required', 'int'],
            'answers.*.type' => ['required', new Enum(QuestionType::class)],
            'answers.*.answer' => ['required'],
            'answers.*.note' => ['nullable', 'string', 'max:225'],
        ]);

        try {
            $therapy = $this->therapyService->get(
                patientId: auth()->id(), id: $validated['therapy_id']
            )->first();

            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            if (! now()->between($therapy->start_date, $therapy->end_date)) {
                return Response::error('Tanggal tidak valid.', 400);
            }

            $record = $this->getRecordByType($validated['record_type'], $therapy->id, $validated['record_id']);
            if (! $record) {
                return Response::error("Data {$validated['record_type']} tidak ditemukan.", 404);
            }

            foreach ($validated['answers'] as $answerData) {
                $answer = $this->answerService->get($answerData['id']);
                if (! $answer) {
                    return Response::error("Jawaban tidak ditemukan (ID: {$answerData['id']}).", 404);
                }

                $question = $this->questionService->get($validated['record_type'], $answerData['question_id'])->first();
                if (! $question) {
                    return Response::error("Pertanyaan tidak ditemukan (ID: {$answerData['question_id']}).", 404);
                }

                if ($question->type->value != $answerData['type']) {
                    return Response::error("Tipe jawaban tidak sesuai untuk pertanyaan ID: {$question->id}", 422);
                }

                $answer->update([
                    'type' => $answerData['type'],
                    'answer' => $answerData['answer'],
                    'note' => $answerData['note'],
                ]);
            }

            return Response::success(null, 'Jawaban berhasil diubah.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
