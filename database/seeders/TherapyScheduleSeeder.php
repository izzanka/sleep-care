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
                'Psikolog memberikan penjelasan mengenai gambaran terapi, insomnia, sleep hygiene, dan pendekatan ACT-I.',
                'Psikolog menyusun kesepakatan bersama dengan pasien',
                'Psikolog meminta pasien untuk mengisi sleep diary setiap hari selama sesi terapi.',
                'Psikolog meminta pasien untuk menyampaikan permasalahan insomnia yang dialami.',
            ],
            [
                'Psikolog meminta pasien untuk mengisi identify value.',
                'Psikolog mengajarkan teknik pernapasan kepada pasien dan membimbing latihan praktiknya.',
            ],
            [
                'Psikolog menjelaskan berbagai bentuk pikiran yang mengganggu.',
                'Psikolog meminta pasien untuk mulai mengisi emotion record dan thought record.',
                'Psikolog membimbing pasien untuk mempraktikkan cara-cara mengatasi pikiran yang mengganggu.',
                'Psikolog mengarahkan pasien untuk melakukan latihan observasi diri sebagai konteks (self-as-context).',
            ],
            [
                'Psikolog menjelaskan penerapan mindfulness dalam kehidupan sehari-hari.',
                'Psikolog membimbing pasien dalam mempraktikkan teknik acceptance dan mindfulness.',
            ],
            [
                'Psikolog menjelaskan konsep committed action.',
                'Psikolog meminta pasien untuk mengisi form committed action berdasarkan nilai-nilai (value) yang dimiliki.',
            ],
            [
                'Psikolog dan pasien mendiskusikan hasil dari committed action yang telah dilakukan.',
                'Psikolog meminta pasien untuk merangkum keseluruhan sesi terapi',
                'Psikolog meminta pasien untuk memberikan umpan balik kepada psikolog dan terhadap proses terapi.',
            ],
        ];

        foreach ($therapyScheduleDescriptions as $index => $therapyScheduleDescription) {
            TherapySchedule::create([
                'therapy_id' => $therapy->id,
                'title' => 'Jadwal Sesi Terapi Ke-'.($index + 1),
                'description' => json_encode($therapyScheduleDescription),
                'date' => fake()->dateTime(),
                'created_at' => now(),
            ]);
        }
    }
}
