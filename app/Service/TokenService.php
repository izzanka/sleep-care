<?php

namespace App\Service;

use Illuminate\Support\Facades\DB;

class TokenService
{
    public function getAvailableOtp(string $email)
    {
        return DB::table('password_reset_tokens')->where('email', $email)
            ->where('expired_at', '>', now())->latest()->first();
    }

    public function checkOtp(string $email, string $token)
    {
        return DB::table('password_reset_tokens')->where('email', $email)
            ->where('token', $token)->latest()->first();
    }

    public function generateOtp()
    {
        return rand(10000, 99999);
    }

    public function storeOtp(string $email, string $otp)
    {
        return DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $otp,
            'created_at' => now(),
            'expired_at' => now()->addMinutes(5),
        ]);
    }

    public function deleteOtp(?string $token = null, ?string $email = null)
    {
        $query = DB::table('password_reset_tokens');

        if ($token) {
            $query->where('token', $token);
        }

        if ($email) {
            $query->where('email', $email);
        }

        return $query->delete();
    }
}
