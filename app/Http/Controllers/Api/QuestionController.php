<?php

namespace App\Http\Controllers\Api;

use App\Enum\RecordType;
use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\QuestionService;
use App\Service\TherapyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

class QuestionController extends Controller
{
    public function __construct(protected QuestionService $questionService,
        protected TherapyService $therapyService) {}

    public function get(Request $request)
    {
        $validated = $request->validate([
            'record_type' => ['required', new Enum(RecordType::class)],
        ]);

        try {

            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $questions = $this->questionService->get($validated['record_type']);

            return Response::success([
                'questions' => $questions,
            ], 'Berhasil mendapatkan data pertanyaan ' . $validated['record_type'] . '.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
