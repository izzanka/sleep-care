<?php

namespace App\Http\Controllers\api;

use App\Enum\OrderStatus;
use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Therapy;
use App\Service\DoctorService;
use App\Service\GeneralService;
use App\Service\OrderService;
use App\Service\TherapyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

class OrderController extends Controller
{
    public function __construct(protected TherapyService $therapyService,
        protected DoctorService $doctorService,
        protected GeneralService $generalService,
        protected OrderService $orderService) {}

    public function get(Request $request)
    {
        $validated = $request->validate([
            'payment_status' => ['required', new Enum(OrderStatus::class)],
        ]);

        try {

            $order = $this->orderService->get(patientId: auth()->id(), payment_status: $validated['payment_status'])->first();
            if (! $order) {
                return Response::error('Order tidak ditemukan', 404);
            }

            return Response::success(new OrderResource($order), 'Berhasil mendapatkan order terapi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'int'],
        ]);

        try {

            $doctor = $this->doctorService->get(id: $validated['doctor_id'])->first();
            if (! $doctor) {
                return Response::error('Psikolog tidak ditemukan.', 404);
            }

            if (! $doctor->user->is_active || ! $doctor->is_available) {
                return Response::error('Psikolog sedang tidak tersedia.', 404);
            }

            //            if ($doctor->therapies()->where('status', TherapyStatus::IN_PROGRESS->value)->exists()) {
            //                return Response::error('Psikolog sedang menjalankan terapi dengan pasien lain.', 404);
            //            }

            if (auth()->user()->therapies()->where('status', TherapyStatus::IN_PROGRESS->value)->exists()) {
                return Response::error('Anda sedang menjalankan terapi.', 404);
            }

            $hasPendingOrder = auth()->user()->therapies()->whereHas('order', function ($query) {
                $query->where('status', OrderStatus::PENDING->value);
            })->exists();

            if ($hasPendingOrder) {
                return Response::error('Anda memiliki order yang sedang menunggu proses pembayaran.', 404);
            }

            $startDate = now();
            $endDate = $startDate->addWeeks(6);
            $general = $this->generalService->get();
            $totalPrice = $general->doctor_fee + $general->application_fee;

            $therapy = Therapy::create([
                'doctor_id' => $validated['doctor_id'],
                'patient_id' => auth()->id(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'doctor_fee' => $general->doctor_fee,
                'application_fee' => $general->application_fee,
            ]);

            $order = Order::create([
                'therapy_id' => $therapy->id,
                'status' => OrderStatus::PENDING->value,
                'total_price' => $totalPrice,
                'payment_status' => OrderStatus::PENDING->value,
            ]);

            return Response::success(new OrderResource($order), 'Berhasil memesan terapi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
