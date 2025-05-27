<?php

namespace Database\Seeders;

use App\Enum\TherapyStatus;
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
        $therapyInProgress = Therapy::where('status', TherapyStatus::IN_PROGRESS->value)->first();
        $therapyCompleted = Therapy::where('status', TherapyStatus::COMPLETED->value)->first();

        $therapyScheduleDescriptions = [
            [
                'Psikolog memberikan penjelasan mengenai gambaran terapi, insomnia, sleep hygiene, dan pendekatan ACT.',
                'Psikolog menyusun kesepakatan bersama dengan pasien',
                'Psikolog mengingatkan pasien untuk mengisi catatan tidur (sleep diary) setiap hari selama terapi berlangsung.',
                'Psikolog meminta pasien untuk menyampaikan permasalahan insomnia yang dialami.',
            ],
            [
                'Psikolog mengingatkan pasien untuk mengisi catatan identifikasi nilai (identify value).',
                'Psikolog mengajarkan teknik pernapasan kepada pasien dan membimbing latihan praktiknya.',
            ],
            [
                'Psikolog menjelaskan berbagai bentuk pikiran yang mengganggu.',
                'Psikolog mengingatkan pasien untuk mengisi catatan emosi (emotion record) dan catatan pikiran (thought record).',
                'Psikolog membimbing pasien untuk mempraktikkan cara-cara mengatasi pikiran yang mengganggu.',
                'Psikolog mengarahkan pasien untuk melakukan latihan observasi diri sebagai konteks (self-as-context).',
            ],
            [
                'Psikolog menjelaskan penerapan mindfulness dalam kehidupan sehari-hari.',
                'Psikolog membimbing pasien dalam mempraktikkan teknik acceptance dan mindfulness.',
            ],
            [
                'Psikolog menjelaskan konsep tindakan berkomitmen (committed action).',
                'Psikolog mengingatkan pasien untuk mengisi catatan tindakan berkomitmen (committed action) berdasarkan nilai-nilai (value) yang pasien miliki.',
            ],
            [
                'Psikolog dan pasien mendiskusikan hasil dari catatan tindakan berkomitmen (committed action) yang telah dilakukan.',
                'Psikolog meminta pasien untuk merangkum keseluruhan sesi terapi',
                'Psikolog meminta pasien untuk memberikan ulasan kepada psikolog dan terhadap proses terapi.',
            ],
        ];

        foreach ([$therapyInProgress, $therapyCompleted] as $therapy) {
            foreach ($therapyScheduleDescriptions as $index => $descriptions) {
                TherapySchedule::create([
                    'therapy_id' => $therapy->id,
                    'title' => 'Jadwal Sesi Terapi '.($index + 1),
                    'description' => json_encode($descriptions),
                    'note' => fake()->sentence,
                    'date' => $therapy->start_date->addWeeks($index),
                    'link' => 'https://meet.google.com/msz-nzny-szk',
                    'time' => fake()->time('H:i'),
                    'created_at' => now(),
                ]);
            }
        }
    }
}
