<?php

namespace App\Http\Controllers\Api;

use App\Enum\UserRole;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use App\Notifications\RegisteredUser;
use App\Service\TokenService;
use App\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function __construct(protected UserService $userService,
        protected TokenService $otpService) {}

    public function request(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        try {

            $checkOtp = $this->otpService->get($validated['email']);
            if ($checkOtp) {
                if (now()->greaterThan($checkOtp->expired_at)) {
                    $this->otpService->delete(email: $validated['email']);
                } else {
                    return Response::error('Kode OTP sudah dikirim sebelumnya dan belum kedaluwarsa.', 400);
                }
            }

            $user = $this->userService->get($validated['email'], UserRole::PATIENT->value, false)->first();
            if (! $user) {
                return Response::error('Akun tidak ditemukan atau sudah terverifikasi.', 404);
            }

            $otp = $this->otpService->generate();
            $this->otpService->store($validated['email'], $otp);

            Mail::to($validated['email'])->send(new OtpMail($otp));

            return Response::success(null, 'Kode OTP berhasil dikirim.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'integer'],
        ]);

        try {

            $user = $this->userService->get($validated['email'], UserRole::PATIENT->value, false)->first();
            if (! $user) {
                return Response::error('Akun tidak ditemukan atau sudah terverifikasi.', 404);
            }

            $otpRecord = $this->otpService->get($validated['email'], $validated['otp']);
            if (! $otpRecord || now()->greaterThan($otpRecord->expired_at)) {
                return Response::error('Kode OTP tidak valid atau sudah kedaluwarsa.', 422);
            }

            $user->update(['email_verified_at' => now()]);
            $this->otpService->delete($validated['otp'], $validated['email']);

            $this->notifyAdmin($user);

            return Response::success(null, 'Kode OTP berhasil diverifikasi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    protected function notifyAdmin(User $user)
    {
        $admin = $this->userService->get(role: UserRole::ADMIN->value)->first();
        $admin->notify(new RegisteredUser($user));
    }
}
