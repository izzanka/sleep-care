<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\Records\IdentifyValueService;
use App\Service\TherapyService;
use Illuminate\Http\Response;

class IdentifyValueController extends Controller
{
    public function __construct(protected IdentifyValueService $identifyValueService,
        protected TherapyService $therapyService) {}

    public function get()
    {
        try {

            $patientId = auth()->id();
            $therapy = $this->therapyService->get(patientId: $patientId, status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $identifyValue = $this->identifyValueService->get($therapy->id);

            return Response::success([
                'identify_value' => $identifyValue,
            ], 'Berhasil mendapatkan data identify value.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
