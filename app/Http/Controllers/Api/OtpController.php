<?php

namespace App\Http\Controllers\Api;

use App\Enum\ModelFilter;
use App\Enum\UserRole;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
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

            $filters = [
                [
                    'operation' => ModelFilter::EQUAL->name,
                    'column' => 'email',
                    'value' => $validated['email'],
                ],
                [
                    'operation' => ModelFilter::MORE_THAN->name,
                    'column' => 'expired_at',
                    'value' => now(),
                ],
            ];

            $checkOtp = $this->otpService->get($filters);
            if ($checkOtp->isNotEmpty()) {
                return Response::error('The OTP has already been sent.', 400);
            }

            $user = $this->getUnverifiedUser($validated['email']);
            if (! $user) {
                return Response::error('Patient account is not found or already verified.', 404);
            }

            $otp = $this->otpService->generateOtp();

            $storeOtp = $this->otpService->storeOtp($validated['email'], $otp);
            if (! $storeOtp) {
                return Response::error('Internal server error', 500);
            }

            Mail::to($validated['email'])->queue(new OtpMail($otp));

            return Response::success(null, 'OTP sent successfully');

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

            $user = $this->getUnverifiedUser($validated['email']);
            if (! $user) {
                return Response::error('Patient account is not found or already verified.', 404);
            }

            $otpRecord = $this->otpService->getOtp($validated['email'], $validated['otp']);
            if (! $otpRecord) {
                return Response::error('Invalid or expired OTP.', 422);
            }

            $user = $this->getUnverifiedUser($validated['email']);

            $updateUser = $user->update(['email_verified_at' => now()]);
            if (! $updateUser) {
                return Response::error('Internal server error', 500);
            }

            $deleteToken = $this->otpService->deleteOtp($validated['otp']);
            if (! $deleteToken) {
                return Response::error('Internal server error', 500);
            }

            return Response::success(null, 'OTP verified successfully');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    protected function getUnverifiedUser(string $email)
    {
        $filters = [
            [
                'operation' => ModelFilter::EQUAL->name,
                'column' => 'email',
                'value' => $email,
            ],
            [
                'operation' => ModelFilter::EQUAL->name,
                'column' => 'role',
                'value' => UserRole::PATIENT->value,
            ],
            [
                'operation' => ModelFilter::EQUAL->name,
                'column' => 'email_verified_at',
                'value' => null,
            ],
        ];

        return $this->userService->get($filters)[0] ?? null;
    }
}
