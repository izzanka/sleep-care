<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\Records\ThoughtRecordService;
use App\Service\TherapyService;
use Illuminate\Http\Response;

class ThoughtRecordController extends Controller
{
    public function __construct(protected ThoughtRecordService $thoughtRecordService,
        protected TherapyService $therapyService) {}

    public function get()
    {
        try {

            $patientId = auth()->id();
            $therapy = $this->therapyService->get(patientId: $patientId, status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $thoughtRecord = $this->thoughtRecordService->get($therapy->id);

            return Response::success([
                'though_record' => $thoughtRecord,
            ], 'Berhasil mendapatkan data thought record.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
