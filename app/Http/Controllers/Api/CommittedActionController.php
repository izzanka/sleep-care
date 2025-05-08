<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\Records\CommittedActionService;
use App\Service\TherapyService;
use Illuminate\Http\Response;

class CommittedActionController extends Controller
{
    public function __construct(protected CommittedActionService $committedActionService,
        protected TherapyService $therapyService) {}

    public function get()
    {
        try {

            $patientId = auth()->id();
            $therapy = $this->therapyService->get(patientId: $patientId, status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $committedAction = $this->committedActionService->get($therapy->id);

            return Response::success([
                'committed_action' => $committedAction,
            ], 'Berhasil mendapatkan data committed action.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
