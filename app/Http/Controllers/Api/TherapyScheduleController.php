<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TherapyScheduleResource;
use App\Service\TherapyScheduleService;
use App\Service\TherapyService;
use Illuminate\Http\Response;

class TherapyScheduleController extends Controller
{
    public function __construct(protected TherapyService $therapyService,
        protected TherapyScheduleService $therapyScheduleService) {}

    public function get(int $therapyId)
    {
        try {

            $therapy = $this->therapyService->get(patientId: auth()->id(), id: $therapyId)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $schedules = $this->therapyScheduleService->get($therapy->id);
            if (! $schedules) {
                return Response::error('Jadwal terapi tidak ditemukan.', 404);
            }

            return Response::success([
                'schedules' => TherapyScheduleResource::collection($schedules),
            ], 'Berhasil mendapatkan data jadwal terapi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
