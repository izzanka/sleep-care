<?php

namespace Database\Seeders;

use App\Models\Therapy;
use App\Models\TherapySchedule;
use Illuminate\Database\Seeder;

class TherapyScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $therapy = Therapy::select('id')->first();

        $therapyScheduleDescriptions = [
            [
                'Psikolog menjelaskan gambaran terapi, insomnia, sleep hygiene dan ACT-I',
                'Menyusun kesepakatan bersama dan meminta pasien untuk mengisi sleep diary setiap hari selama terapi',
                'Psikolog meminta pasien untuk menyampaikan masalah insomnia yang dialami',
            ],
            [
                'Psikolog meminta pasien untuk mengisi identify value yang terdampak dengan adanya insomnia',
                'Psikolog mengajari pasien berlatih teknik pernapasan',
            ],
            [
                'Psikolog menjelaskan bentuk-bentuk pikiran yang mengganggu',
                'Psikolog meminta pasien untuk mulai mengisi emotion record dan thought record',
                'Psikolog meminta pasien untuk mempraktikkan cara-cara mengatasi pikiran mengganggu',
                'Psikolog meminta pasien untuk latihan observasi diri sebagai konteks (self-as-context)',
            ],
            [
                'Psikolog menjelaskan penerapan mindfulness di kehidupan sehari-hari',
                'Psikolog mengajari pasien untuk mempraktikkan teknik acceptance dan mindfulness',
            ],
            [
                'Psikolog menjelaskan konsep committed action',
                'Psikolog meminta pasien untuk mengisi committed action sesuai value yang dimiliki',
            ],
            [
                'Psikolog dan pasien membahas committed action',
                'Pasien merangkum keseluruhan sesi dan memberikan feedback kepada psikolog dan sesi terapi',
            ],
        ];

        foreach ($therapyScheduleDescriptions as $index => $therapyScheduleDescription) {
            TherapySchedule::create([
                'therapy_id' => $therapy->id,
                'title' => 'Pertemuan sesi terapi ke-'.($index + 1),
                'description' => json_encode($therapyScheduleDescription),
                'date' => fake()->dateTime(),
                'created_at' => now(),
            ]);
        }
    }
}
