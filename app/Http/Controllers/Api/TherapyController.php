<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\DoctorService;
use App\Service\GeneralService;
use App\Service\RecordService;
use App\Service\TherapyScheduleService;
use App\Service\TherapyService;
use App\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;
use Mockery\Exception;

class TherapyController extends Controller
{
    public function __construct(protected TherapyService $therapyService,
        protected UserService $userService,
        protected DoctorService $doctorService,
        protected GeneralService $generalService,
        protected TherapyScheduleService $therapyScheduleService,
        protected RecordService $recordService) {}

    public function get(Request $request)
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(TherapyStatus::class)],
        ]);

        try {

            $therapies = $this->therapyService->get(patientId: auth()->id(), status: $validated['status']);
            if (! $therapies) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            if ($validated['status'] === TherapyStatus::IN_PROGRESS->value) {
                $therapy = $therapies->first();

                return Response::success($therapy, 'Berhasil mendapatkan data terapi.');
            }

            return Response::success([
                'therapies' => $therapies,
            ], 'Berhasil mendapatkan data terapi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(TherapyStatus::class)],
        ]);

        try {

            $therapies = $this->therapyService->get(patientId: auth()->id(), status: $validated['status']);
            if (! $therapies) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            if ($validated['status'] == TherapyStatus::IN_PROGRESS) {
                $therapies->first();
            }

            return Response::success($therapies, 'Berhasil mendapatkan data terapi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function storeRating(Request $request)
    {
        $validated = $request->validate([
            'therapy_id' => ['required', 'int'],
            'rating' => ['required', 'int'],
            'comment' => ['nullable', 'string', 'max:225'],
        ]);

        try {

            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::COMPLETED->value, id: $validated['therapy_id'])->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $therapy->doctor->rateOnce($validated['rating']);

            if ($validated['comment']) {
                $therapy->update([
                    'comment' => $validated['comment'],
                ]);
            }

            return Response::success($validated, 'Berhasil memberikan rating.');

        } catch (Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
