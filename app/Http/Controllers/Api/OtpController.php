<?php

namespace App\Http\Controllers\Api;

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

            $checkOtp = $this->otpService->getAvailableOtp($validated['email']);
            if ($checkOtp) {
                if (now()->greaterThan($checkOtp->expired_at)) {
                    $this->otpService->deleteOtp(email: $validated['email']);
                }

                return Response::error('Kode OTP sudah dikirim sebelumnya dan belum kedaluwarsa.', 400);
            }

            $user = $this->userService->getUnverifiedPatient($validated['email']);
            if (! $user) {
                return Response::error('Akun tidak ditemukan atau sudah terverifikasi.', 404);
            }

            $otp = $this->otpService->generateOtp();
            $this->otpService->storeOtp($validated['email'], $otp);

            Mail::to($validated['email'])->queue(new OtpMail($otp));

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

            $user = $this->userService->getUnverifiedPatient($validated['email']);
            if (! $user) {
                return Response::error('Akun tidak ditemukan atau sudah terverifikasi.', 404);
            }

            $otpRecord = $this->otpService->checkOtp($validated['email'], $validated['otp']);
            if (! $otpRecord || now()->greaterThan($otpRecord->expired_at)) {
                return Response::error('Kode OTP tidak valid atau sudah kedaluwarsa.', 422);
            }

            $user->update(['email_verified_at' => now()]);
            $this->otpService->deleteOtp($validated['otp'], $validated['email']);

            $this->notifyAdmin($user);

            return Response::success(null, 'Kode OTP berhasil diverifikasi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    protected function notifyAdmin(User $user)
    {
        $admin = $this->userService->getAdmin();
        $admin->notify(new RegisteredUser($user));
    }
}
