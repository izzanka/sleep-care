<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\Records\SleepDiaryService;
use App\Service\TherapyService;
use Illuminate\Http\Response;

class SleepDiaryController extends Controller
{
    public function __construct(protected SleepDiaryService $sleepDiaryService,
        protected TherapyService $therapyService) {}

    public function get()
    {
        try {

            $patientId = auth()->id();
            $therapy = $this->therapyService->get(patientId: $patientId, status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $sleepDiaries = $this->sleepDiaryService->get($therapy->id);

            return Response::success([
                'sleep_diaries' => $sleepDiaries,
            ], 'Berhasil mendapatkan data sleep diary.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getDetail(int $id)
    {
        try {

            $patientId = auth()->id();
            $therapy = $this->therapyService->get(patientId: $patientId, status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $sleepDiary = $this->sleepDiaryService->find($id);

            return Response::success([
                'sleep_diary' => $sleepDiary,
            ], 'Berhasil mendapatkan data detail sleep diary.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
