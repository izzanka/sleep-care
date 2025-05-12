<?php

namespace App\Service;

use App\Models\TherapySchedule;

class TherapyScheduleService
{
    public function get(int $therapyId)
    {
        return TherapySchedule::where('therapy_id', $therapyId)->oldest()->get();
    }

    public function getByID(int $scheduleId)
    {
        return TherapySchedule::find($scheduleId);
    }

    public function generate(int $therapyId)
    {
        $therapyScheduleDescriptions = [
            [
                'Psikolog memberikan penjelasan mengenai gambaran terapi, insomnia, sleep hygiene, dan pendekatan ACT.',
                'Psikolog menyusun kesepakatan bersama dengan pasien',
                'Psikolog meminta pasien untuk mengisi sleep diary setiap hari selama terapi berlangsung.',
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
                'Psikolog meminta pasien untuk mengisi committed action berdasarkan nilai-nilai (value) yang dimiliki.',
            ],
            [
                'Psikolog dan pasien mendiskusikan hasil dari committed action yang telah dilakukan.',
                'Psikolog meminta pasien untuk merangkum keseluruhan sesi terapi',
                'Psikolog meminta pasien untuk memberikan umpan balik kepada psikolog dan terhadap proses terapi.',
            ],
        ];

        foreach ($therapyScheduleDescriptions as $index => $descriptions) {
            TherapySchedule::create([
                'therapy_id' => $therapyId,
                'title' => 'Jadwal Sesi Terapi '.($index + 1),
                'description' => json_encode($descriptions),
            ]);
        }
    }
}
