<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;

class HimpsiService
{
    public function get(string $email)
    {
        $response = Http::timeout(env('HIMPSI_TIMEOUT'))
            ->get(env('HIMPSI_URL'), ['filter[email]' => $email]);

        if ($response->failed()) {
            return false;
        }

        $data = $response->json('data.data') ?? [];

        if (empty($data)) {
            return [];
        }

        return collect($data)->map(function ($item) {
            return [
                'registered_year' => $item['tahun_terdaftar'] ?? null,
                'phone' => $item['no_hp_decode'] ?? null,
            ];
        })->all();
    }
}
