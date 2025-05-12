<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\RecordService;
use App\Service\TherapyService;
use Illuminate\Http\Response;

class RecordController extends Controller
{
    public function __construct(protected RecordService $recordService,
        protected TherapyService $therapyService) {}

    public function getCommittedActions()
    {
        try {
            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $committedActions = $this->recordService->getCommittedActions($therapy->id);
            if (! $committedActions) {
                return Response::error('Data committed action tidak ditemukan.', 404);
            }

            return Response::success([
                'committed_actions' => $committedActions,
            ], 'Berhasil mendapatkan data committed action.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getEmotionRecords()
    {
        try {
            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $emotionRecords = $this->recordService->getEmotionRecords($therapy->id);
            if (! $emotionRecords) {
                return Response::error('Data committed action tidak ditemukan.', 404);
            }

            return Response::success([
                'emotion_records' => $emotionRecords,
            ], 'Berhasil mendapatkan data emotion record.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getIdentifyValues()
    {
        try {
            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $identifyValues = $this->recordService->getIdentifyValues($therapy->id);
            if (! $identifyValues) {
                return Response::error('Data identify value tidak ditemukan.', 404);
            }

            return Response::success([
                'identify_value' => $identifyValues,
            ], 'Berhasil mendapatkan data identify value.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getSleepDiaries()
    {
        try {
            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $sleepDiaries = $this->recordService->getSleepDiaries($therapy->id);
            if (! $sleepDiaries) {
                return Response::error('Data sleep diary tidak ditemukan.', 404);
            }

            return Response::success([
                'sleep_diaries' => $sleepDiaries,
            ], 'Berhasil mendapatkan data sleep diary.');

        } catch (\Exception $exception) {
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

            $sleepDiary = $this->recordService->getSleepDiaryByID($id, $therapy->id);
            if (! $sleepDiary) {
                return Response::error('Data sleep diary tidak ditemukan.', 404);
            }

            return Response::success($sleepDiary, 'Berhasil mendapatkan data detail sleep diary.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getThoughtRecords()
    {
        try {
            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $thoughtRecords = $this->recordService->getThoughtRecords($therapy->id);
            if (! $thoughtRecords) {
                return Response::error('Data thought record tidak ditemukan.', 404);
            }

            return Response::success([
                'though_records' => $thoughtRecords,
            ], 'Berhasil mendapatkan data thought record.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
