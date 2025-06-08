<?php

namespace App\Http\Controllers\Api;

use App\Enum\OrderStatus;
use App\Enum\TherapyStatus;
use App\Enum\UserRole;
use App\Http\Controllers\Controller;
use App\Notifications\OrderedTherapy;
use App\Service\GeneralService;
use App\Service\OrderService;
use App\Service\RecordService;
use App\Service\TherapyScheduleService;
use App\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Transaction;
use Mockery\Exception;

class MidtransController extends Controller
{
    protected $serverKey;

    protected $isProduction;

    protected $apiUrl;

    public function __construct(protected UserService $userService,
        protected TherapyScheduleService $therapyScheduleService,
        protected RecordService $recordService,
        protected OrderService $orderService,
        protected GeneralService $generalService)
    {
        $this->serverKey = config('services.midtrans.server_key');
        $this->isProduction = config('services.midtrans.is_production', false);
        $this->apiUrl = 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        Config::$serverKey = $this->serverKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = false;
        Config::$is3ds = false;
    }

    public function charge(Request $request)
    {
        $validated = $request->validate([
            'transaction_details.order_id' => ['required'],
            'transaction_details.gross_amount' => ['required', 'int'],
            'customer_details.email' => ['required', 'string', 'email'],
        ]);

        $orderId = $validated['transaction_details']['order_id'];
        $grossAmount = $validated['transaction_details']['gross_amount'];
        $email = $validated['customer_details']['email'];

        $order = $this->orderService->get(id: $orderId)->first();
        if (! $order) {
            return Response::error('Order tidak ditemukan.', 404);
        }

        if ($order->payment_type != null && $order->payment_id != null) {
            return Response::error('Pembayaran untuk order ini sudah dilakukan.', 404);
        }

        $patient = $this->userService->get(email: $email, role: UserRole::PATIENT->value, verified: true, is_active: true)->first();
        if (! $patient) {
            return Response::error('Customer tidak ditemukan.', 404);
        }

        if ($order->therapy->patient->email != $email) {
            return Response::error('Data customer tidak sama dengan yang melakukan pembayaran.', 400);
        }

        $general = $this->generalService->get();
        $expectedAmount = $general->doctor_fee + $general->application_fee;
        if ($grossAmount != $expectedAmount) {
            return Response::error('Gross amount tidak valid.', 400);
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode($this->serverKey.':'),
        ])->post($this->apiUrl, $request->all());

        return response($response->body(), $response->status());
    }

    public function notification(Request $request)
    {
        try {
            DB::beginTransaction();

            $notification = new Notification;
            $status = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $paymentId = $notification->transaction_id;
            $orderId = $notification->order_id;

            $order = $this->orderService->get(payment_status: OrderStatus::PENDING->value, id: $orderId)->first();
            if (! $order) {
                DB::rollBack();

                return Response::error('Order tidak ditemukan.', 400);
            }

            $this->updateOrderStatus($order, $status, $paymentType, $paymentId);
            DB::commit();

            return Response::success($notification, 'Notifikasi midtrans berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();

            return Response::error($e->getMessage(), 500);
        }
    }

    private function updateOrderStatus($order, string $status, string $paymentType, string $paymentId)
    {
        $order->payment_type = $paymentType;
        $order->payment_id = $paymentId;

        switch ($status) {
            case 'settlement':
                $this->handleSettlement($order);
                break;
            case 'pending':
                $this->setOrderStatus($order, OrderStatus::PENDING, OrderStatus::PENDING);
                break;
            case 'deny':
                $this->setOrderStatus($order, OrderStatus::FAILURE, OrderStatus::DENY);
                break;
            case 'cancel':
                $this->setOrderStatus($order, OrderStatus::FAILURE, OrderStatus::CANCEL);
                break;
            case 'expire':
                $this->setOrderStatus($order, OrderStatus::FAILURE, OrderStatus::EXPIRE);
                break;
        }

        $order->save();
    }

    private function handleSettlement($order): void
    {
        $this->setOrderStatus($order, OrderStatus::SUCCESS, OrderStatus::SETTLEMENT);

        $therapy = $order->therapy;
        $therapy->status = TherapyStatus::IN_PROGRESS->value;
        $therapy->save();

        $doctorUser = $therapy->doctor->user;
        $patientUser = $therapy->patient;

        $doctorUser->update(['is_therapy_in_progress' => true]);
        $patientUser->update(['is_therapy_in_progress' => true]);

        $adminUser = $this->userService->get(role: UserRole::ADMIN->value)->first();

        $doctorUser->increment('balance', $therapy->doctor_fee);
        $adminUser->increment('balance', $therapy->application_fee);

        $doctorUser->notify(new OrderedTherapy($therapy));
        $adminUser->notify(new OrderedTherapy($therapy));

        $this->therapyScheduleService->generate($therapy->id);

        $this->recordService->generateSleepDiaries($therapy->id, $therapy->start_date);
        $this->recordService->generateIdentifyValue($therapy->id);
        $this->recordService->generateEmotionRecord($therapy->id);
        $this->recordService->generateThoughtRecord($therapy->id);
        $this->recordService->generateCommittedAction($therapy->id);
    }

    private function setOrderStatus($order, OrderStatus $status, OrderStatus $paymentStatus)
    {
        $order->status = $status->value;
        $order->payment_status = $paymentStatus->value;
    }

    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required'],
        ]);

        try {

            $order = $this->orderService->get(payment_status: OrderStatus::PENDING->value, id: $validated['order_id'])->first();
            if (! $order) {
                return Response::error('Order tidak ditemukan.', 404);
            }

            $response = null;

            if ($order->payment_id) {
                $response = Transaction::cancel($validated['order_id']);
                $order->payment_id = $response->transaction_id ?? $order->payment_id;
            }

            $order->payment_status = OrderStatus::CANCEL->value;
            $order->status = OrderStatus::FAILURE->value;
            $order->save();

            return Response::success($response, 'Transaksi berhasil dibatalkan.');

        } catch (Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function check(string $orderId)
    {
        try {

            return Transaction::status($orderId);

        } catch (Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        try {

            DB::beginTransaction();

            $transaction = $this->check($validated['order_id']);

            $status = $transaction->transaction_status;
            $paymentType = $transaction->payment_type;
            $paymentId = $transaction->transaction_id;
            $orderId = $transaction->order_id;

            $order = $this->orderService->get(payment_status: OrderStatus::PENDING->value, id: $orderId)->first();
            if (! $order) {
                DB::rollBack();

                return Response::error('Order tidak ditemukan.', 400);
            }

            $this->updateOrderStatus($order, $status, $paymentType, $paymentId);
            DB::commit();

            return Response::success($order, 'Update midtrans berhasil.');
        } catch (Exception $exception) {
            DB::rollBack();

            return Response::error($exception->getMessage(), 500);
        }
    }
}
