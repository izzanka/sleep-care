<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function request(Request $request)
    {
        $validated = $this->validateEmailOnly($request);

        $user = $this->getUnverifiedActiveUser($validated['email']);
        if (!$user) {
            return Response::error('Patient account is not found or already verified.', 404);
        }

        $otp = $this->generateOtp();

        $this->storeOtp($validated['email'], $otp);

        Mail::to($validated['email'])->queue(new OtpMail($otp));

        return Response::success(null, 'OTP sent successfully');
    }

    public function verify(Request $request)
    {
        $validated = $this->validateEmailAndOtp($request);

        $user = $this->getUnverifiedActiveUser($validated['email']);
        if (!$user) {
            return Response::error('Patient account is not found or already verified.', 404);
        }

        $otpRecord = $this->getValidOtp($validated['email'], $validated['otp']);
        if (!$otpRecord) {
            return Response::error('Invalid or expired OTP.', 422);
        }

        $this->verifyUserEmail($validated['email']);
        $this->deleteOtp($otpRecord->token);

        return Response::success(null, 'OTP verified successfully');
    }

    protected function validateEmailOnly(Request $request)
    {
        return $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);
    }

    protected function validateEmailAndOtp(Request $request)
    {
        return $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'numeric'],
        ]);
    }

    protected function getUnverifiedActiveUser(string $email)
    {
        return User::where('email', $email)
            ->whereNull('email_verified_at')
            ->first();
    }

    protected function generateOtp()
    {
        return rand(10000, 99999);
    }

    protected function storeOtp(string $email, string $otp)
    {
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $otp,
            'created_at' => now(),
            'expired_at' => now()->addMinutes(5),
        ]);
    }

    protected function getValidOtp(string $email, string $otp)
    {
        return DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $otp)
            ->where('expired_at', '>=', now())
            ->latest()
            ->first();
    }

    protected function verifyUserEmail(string $email)
    {
        User::where('email', $email)
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    protected function deleteOtp(string $token)
    {
        DB::table('password_reset_tokens')->where('token', $token)->delete();
    }
}
