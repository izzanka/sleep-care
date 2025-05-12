<?php

namespace App\Http\Controllers\Api;

use App\Enum\OrderStatus;
use App\Enum\TherapyStatus;
use App\Enum\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Therapy;
use App\Notifications\OrderedTherapy;
use App\Service\DoctorService;
use App\Service\GeneralService;
use App\Service\RecordService;
use App\Service\TherapyScheduleService;
use App\Service\TherapyService;
use App\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

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

            $therapy = $this->therapyService->get(patientId: auth()->id(), status: $validated['status'])->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            return Response::success([
                'therapy' => $therapy,
            ], 'Berhasil mendapatkan data terapi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function order(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'int'],
        ]);

        try {

            $doctor = $this->doctorService->get(id: $validated['doctor_id'])->first();
            if (! $doctor) {
                return Response::error('Psikolog tidak ditemukan.', 404);
            }

            if (! $doctor->user->is_active) {
                return Response::error('Psikolog sedang tidak menerima terapi.', 404);
            }

            if ($doctor->therapies()->where('status', TherapyStatus::IN_PROGRESS->value)->exists()) {
                return Response::error('Psikolog sedang menjalankan terapi dengan pasien lain.', 404);
            }

            $startDate = now();
            $general = $this->generalService->get();
            $totalPrice = $general->doctor_fee + $general->application_fee;

            $therapy = Therapy::create([
                'doctor_id' => $validated['doctor_id'],
                'patient_id' => auth()->id(),
                'start_date' => $startDate,
                'end_date' => $startDate->addWeeks(6),
                'status' => TherapyStatus::IN_PROGRESS->value,
                'doctor_fee' => $general->doctor_fee,
                'application_fee' => $general->application_fee,
            ]);

            Order::create([
                'therapy_id' => $therapy->id,
                'status' => OrderStatus::SETTLEMENT->value,
                'total_price' => $totalPrice,
                'payment_status' => OrderStatus::SETTLEMENT->value,
                'payment_method' => 'Virtual Bank Account',
                'payment_token' => 'payment_token',
            ]);

            $doctor->user->increment('balance', $general->doctor_fee);

            $admin = $this->userService->get(role: UserRole::DOCTOR->value)->first();
            $admin->increment('balance', $general->application_fee);

            $doctor->user->notify(new OrderedTherapy($therapy));
            $admin->notify(new OrderedTherapy($therapy));

            $this->therapyScheduleService->generate($therapy->id);
            $this->recordService->generateSleepDiaries($therapy->id, $startDate);
            $this->recordService->generateIdentifyValue($therapy->id);
            $this->recordService->generateEmotionRecord($therapy->id);
            $this->recordService->generateThoughtRecord($therapy->id);
            $this->recordService->generateCommittedAction($therapy->id);

            return Response::success(null, 'Berhasil memesan terapi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
