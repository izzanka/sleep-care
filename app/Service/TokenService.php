<?php

namespace App\Service;

use Illuminate\Support\Facades\DB;

class TokenService
{
    public function get(?string $email = null, ?string $token = null)
    {
        $query = DB::table('password_reset_tokens');

        if ($email) {
            $query->where('email', $email);
        }

        if ($token) {
            $query->where('token', $token);
        }

        return $query->latest()->first();
    }

    public function generate()
    {
        return rand(10000, 99999);
    }

    public function store(string $email, string $otp)
    {
        return DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $otp,
            'created_at' => now(),
            'expired_at' => now()->addMinutes(5),
        ]);
    }

    public function delete(?string $token = null, ?string $email = null)
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
