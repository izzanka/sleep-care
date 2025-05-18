<?php

namespace App\Http\Controllers\Api;

use App\Enum\QuestionType;
use App\Enum\RecordType;
use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Service\AnswerService;
use App\Service\QuestionService;
use App\Service\RecordService;
use App\Service\TherapyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Mockery\Exception;

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
            'record_type' => ['required', new Enum(RecordType::class)],
        ]);

        try {
            $therapy = $this->therapyService
                ->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)
                ->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            if($validated['record_type'] === RecordType::SLEEP_DIARY->value){
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

            $answers = collect($record->questionAnswers)->map(fn ($qa) => [
                'question_id' => $qa->question_id,
                'answer' => $qa->answer,
            ]);

            return Response::success([
                'id' => $record->id,
                'therapy_id' => $therapy->id,
                'questions' => $questions,
                'answers' => $answers,
            ], "Berhasil mendapatkan data {$validated['record_type']}.");

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

//    public function store(Request $request)
//    {
//        $validated = $request->validate([
//            'record_type' => ['required', new Enum(RecordType::class)],
//            'record_id' => ['required', 'int'],
//            'answers' => ['required', 'array'],
//            'answers.*.question_id' => ['required', 'int'],
//            'answers.*.type' => ['required', new Enum(QuestionType::class)],
//            'answers.*.answer' => ['required'],
//            'answers.*.note' => ['nullable', 'string', 'max:225'],
//        ]);
//
//        try {
//            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
//            if (! $therapy) {
//                return Response::error('Terapi tidak ditemukan.', 404);
//            }
//
//            $record = $this->getRecordByType($validated['record_type'], $therapy->id, $validated['record_id']);
//            if (! $record) {
//                return Response::error("Data {$validated['record_type']} tidak ditemukan.", 404);
//            }
//
//            $savedAnswers = [];
//
//            DB::beginTransaction();
//
//            foreach ($validated['answers'] as $answerData) {
//                $question = $this->questionService->get($validated['record_type'], $answerData['question_id'])->first();
//                if (! $question) {
//                    DB::rollBack();
//
//                    return Response::error('Data pertanyaan tidak ditemukan (ID: '.$answerData['question_id'].').', 404);
//                }
//
//                if ($question->type->value != $answerData['type']) {
//                    DB::rollBack();
//
//                    return Response::error('Tipe jawaban tidak sesuai untuk pertanyaan ID: '.$question->id, 422);
//                }
//
//                $answer = Answer::create([
//                    'type' => $answerData['type'],
//                    'answer' => $answerData['answer'],
//                    'note' => $answerData['note'],
//                ]);
//
//                $table = $validated['record_type'].'_question_answer';
//
//                DB::table($table)->insert([
//                    $validated['record_type'].'_id' => $record->id,
//                    'question_id' => $question->id,
//                    'answer_id' => $answer->id,
//                ]);
//
//                $savedAnswers[] = $answer;
//            }
//
//            DB::commit();
//
//            return Response::success($savedAnswers, 'Berhasil menyimpan semua jawaban '.$validated['record_type'].'.');
//
//        } catch (\Exception $exception) {
//            DB::rollBack();
//
//            return Response::error($exception->getMessage(), 500);
//        }
//    }

    public function getSleepDiaries()
    {
        try {

            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
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

        } catch (Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getSleepDiaryByID(int $id)
    {
        try {

            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $sleepDiary = $this->recordService->getSleepDiaryByID($therapy->id, $id);
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
                'questions' => $questions,
                'answers' => $answers,
            ], 'Berhasil mendapatkan data detail sleep_diary.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
                patientId: auth()->id(),
                status: TherapyStatus::IN_PROGRESS->value
            )->first();

            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $record = $this->getRecordByType($validated['record_type'], $therapy->id, $validated['record_id']);
            if (! $record) {
                return Response::error("Data {$validated['record_type']} tidak ditemukan.", 404);
            }

            $table = $validated['record_type'].'_question_answer';

            $savedAnswers = [];

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

                $pivot = DB::table($table)
                    ->where($validated['record_type'].'_id', $record->id)
                    ->where('question_id', $question->id)
                    ->first();

                if ($pivot) {
                    $answer = Answer::find($pivot->answer_id);
                    $answer->update([
                        'type' => $answerData['type'],
                        'answer' => $answerData['answer'],
                        'note' => $answerData['note'],
                    ]);
                } else {
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

                $savedAnswers[] = $answer;
            }

            DB::commit();

            return Response::success($savedAnswers, 'Jawaban berhasil disimpan.');

        } catch (\Exception $exception) {
            DB::rollBack();
            return Response::error($exception->getMessage(), 500);
        }
    }

}
