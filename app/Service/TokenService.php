<?php

namespace App\Service;

use App\Enum\ModelFilter;
use Illuminate\Support\Facades\DB;

class TokenService
{
    public function get(?array $filters = null)
    {
        $query = DB::table('password_reset_tokens');

        if ($filters) {
            foreach ($filters as $filter) {
                switch ($filter['operation']) {
                    case ModelFilter::EQUAL->name:
                        $query->where($filter['column'], $filter['value']);
                        break;

                    case ModelFilter::MORE_THAN->name:
                        $query->where($filter['column'], ModelFilter::MORE_THAN->value, $filter['value']);
                        break;

                    case ModelFilter::LESS_THAN->name:
                        $query->where($filter['column'], ModelFilter::LESS_THAN->value, $filter['value']);
                        break;

                    case ModelFilter::ORDER_BY->name:
                        $query->orderBy($filter['column'], $filter['value']);
                        break;
                }
            }
        }

        return $query->get();
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

    public function getOtp(string $email, string $otp)
    {
        $filters = [
            [
                'operation' => ModelFilter::EQUAL->name,
                'column' => 'email',
                'value' => $email,
            ],
            [
                'operation' => ModelFilter::EQUAL->name,
                'column' => 'token',
                'value' => $otp,
            ],
            [
                'operation' => ModelFilter::MORE_THAN->name,
                'column' => 'expired_at',
                'value' => now(),
            ],
        ];

        return $this->get($filters)[0] ?? null;
    }

    public function deleteOtp(string $token)
    {
        return DB::table('password_reset_tokens')->where('token', $token)->delete();
    }
}
