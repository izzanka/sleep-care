<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\Records\EmotionRecordService;
use App\Service\TherapyService;
use Illuminate\Http\Response;

class EmotionRecordController extends Controller
{
    public function __construct(protected EmotionRecordService $emotionRecordService,
        protected TherapyService $therapyService) {}

    public function get()
    {
        try {

            $patientId = auth()->id();
            $therapy = $this->therapyService->get(patientId: $patientId, status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $emotionRecord = $this->emotionRecordService->get($therapy->id);

            return Response::success([
                'emotion_record' => $emotionRecord,
            ], 'Berhasil mendapatkan data emotion record.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
