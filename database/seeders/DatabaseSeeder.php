<?php

namespace Database\Seeders;

use App\Enum\OrderStatus;
use App\Enum\QuestionType;
use App\Enum\RecordType;
use App\Enum\TherapyStatus;
use App\Models\Answer;
use App\Models\Doctor;
use App\Models\Order;
use App\Models\Question;
use App\Models\SleepDiary;
use App\Models\Therapy;
use App\Models\TherapySchedule;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->patient()->count(30)->create();

        User::factory()->doctor()->count(30)->create()->each(function ($user) {
            Doctor::factory()->create([
                'user_id' => $user->id,
            ]);
        });

        $admin = User::factory()->admin()->create();
        $userPatient = User::factory()->patient()->create();
        $userDoctor = User::factory()->doctor()->create([
            'name' => 'psikolog',
            'email' => 'psikolog@sleepcare.com',
        ]);

        $doctor = Doctor::factory()->create([
            'user_id' => $userDoctor->id,
            'name_title' => fake()->titleMale().' '.$userDoctor->name,
        ]);

        $therapy = Therapy::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $userPatient->id,
            'status' => TherapyStatus::IN_PROGRESS->value,
        ]);

        $therapyScheduleDescriptions = [
            [
                'Psikolog menjelaskan gambaran terapi, insomnia, sleep hygiene dan ACT-I',
                'Menyusun kesepakatan bersama dan mengingatkan pasien untuk mengisi sleep diary selama terapi',
                'Setiap pasien diminta untuk menyampaikan masalah insomnia yang dialami'
            ],
            [
                'Psikolog meminta pasien untuk mengisi identify value yang terdampak dengan adanya insomnia',
                'Psikolog mengajari pasien berlatih teknik pernapasan'
            ],
            [
                'Psikolog menjelaskan bentuk-bentuk pikiran yang mengganggu',
                'Psikolog meminta pasien untuk mulai mengisi emotion record dan thought record',
                'Psikolog meminta pasien untuk mempraktikkan cara-cara mengatasi pikiran mengganggu',
                'Psikolog meminta pasien untuk latihan observasi diri sebagai konteks (self-as-context)'
            ],
            [
                'Psikolog menjelaskan penerapan mindfulness di kehidupan sehari-hari',
                'Psikolog mengajari pasien untuk mempraktikkan teknik acceptance dan mindfulness'
            ],
            [
                'Psikolog menjelaskan konsep committed action',
                'Psikolog meminta pasien untuk mengisi committed action sesuai value yang dimiliki'
            ],
            [
                'Psikolog dan pasien membahas committed action',
                'Pasien merangkum keseluruhan sesi dan memberikan feedback kepada psikolog dan sesi terapi'
            ],
        ];

        foreach ($therapyScheduleDescriptions as $index => $therapyScheduleDescription) {
            TherapySchedule::create([
                'therapy_id' => $therapy->id,
                'title' => 'Pertemuan sesi terapi ke-' . ($index + 1),
                'description' => json_encode($therapyScheduleDescription),
                'date' => fake()->dateTime(),
                'created_at' => now(),
            ]);
        }

        Order::factory()->create([
            'therapy_id' => $therapy->id,
            'status' => OrderStatus::SUCCESS->value,
        ]);

        $admin->deposit(20000);
        $userDoctor->deposit(350000);

        $sleepDiaryTitleParents = [
            'Apakah kamu tidur siang?',
            'Apakah kamu mengkonsumsi obat tidur?',
        ];

        foreach ($sleepDiaryTitleParents as $sleepDiaryTitleParent) {
            Question::create([
                'question' => $sleepDiaryTitleParent,
                'type' => QuestionType::BINARY->value,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'note' => 'Siang',
                'is_parent' => true,
                'created_at' => now(),
            ]);
        }

        $firstSleepDiaryTitleChilds = [
            'Berapa lama? (dalam menit)',
            'Pukul berapa?',
        ];

        foreach ($firstSleepDiaryTitleChilds as $firstSleepDiaryTitleChild) {
            Question::create([
                'question' => $firstSleepDiaryTitleChild,
                'type' => QuestionType::OPEN->value,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'parent_id' => 1,
                'note' => 'Siang',
                'created_at' => now(),
            ]);
        }

        $secondSleepDiaryTitleChilds = [
            'Apa jenis obatnya?',
            'Berapa banyak?',
            'Pukul berapa?',
        ];

        foreach ($secondSleepDiaryTitleChilds as $secondSleepDiaryTitleChild) {
            Question::create([
                'question' => $secondSleepDiaryTitleChild,
                'type' => QuestionType::OPEN->value,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'parent_id' => 2,
                'note' => 'Siang',
                'created_at' => now(),
            ]);
        }

        $sleepDiaryBinaryQuestions = [
            'Apakah kamu mengkonsumsi kafein (contoh: kopi, teh, soda, coklat, minuman berenergi) setelah pukul 18.00?',
            'Apakah kamu mengkonsumsi alkohol setelah pukul 18.00?',
            'Apakah kamu menggunakan nikotin (contoh: rokok) setelah pukul 18.00?',
            'Apakah kamu berolahraga?',
            'Apakah kamu mengkonsumsi makanan berat atau snack setelah pukul 18.00?',
            'Apakah kamu mengantuk sepanjang hari?',
        ];

        foreach ($sleepDiaryBinaryQuestions as $sleepDiaryBinaryQuestion) {
            Question::create([
                'question' => $sleepDiaryBinaryQuestion,
                'type' => QuestionType::BINARY->value,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'note' => 'Siang',
                'created_at' => now(),
            ]);
        }

        $sleepDiaryOpenQuestions = [
            'Pukul berapa kamu mulai mematikan lampu untuk mulai tidur?',
            'Pukul berapa kamu bangun tidur?',
            'Berapa total jam kamu tidur? (dalam jam)',
            'Berapa kali kamu terbangun di malam hari?',
            'Isilah skala kualitas tidurmu (dalam skala 1-5, 1 sangat tidak berkualitas, 5 sangat berkualitas)',
            'Apakah kamu merasa tidurmu cukup?',
        ];

        foreach ($sleepDiaryOpenQuestions as $sleepDiaryOpenQuestion) {
            Question::create([
                'question' => $sleepDiaryOpenQuestion,
                'type' => QuestionType::BINARY->value,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'note' => 'Malam',
                'created_at' => now(),
            ]);
        }

        $identifyValueTitleParents = [
            'Skala Kepentingan, Seberapa penting area ini untuk Anda? Skala 1 - 10 (1 = Sangat tidak penting, 10 = Sangat penting)',
            'Aku ingin jadi pribadi yang, Contoh: Pengisian di area karir: aku ingin jadi orang yang menguasai bidang pekerjaan',
            'Skor Kesuaian, Seberapa sesuai kondisi Anda saat ini dengan pribadi yang Anda inginkan? Skala 1 - 10 (1 = Sangat tidak sesuai, 10 = Sangat sesuai)',
        ];

        foreach ($identifyValueTitleParents as $index => $identifyValueTitleParent) {
            Question::create([
                'question' => $identifyValueTitleParent,
                'type' => $index == 0 || $index == 2 ? QuestionType::SCALE->value : QuestionType::OPEN->value,
                'record_type' => RecordType::IDENTIFY_VALUE->value,
                'is_parent' => true,
                'created_at' => now(),
            ]);
        }

        $thoughtRecordQuestions = [
            'Tanggal/Jam',
            'Kejadian atau situasi',
            'Pemikiran yang muncul',
        ];

        foreach ($thoughtRecordQuestions as $thoughtRecordQuestion) {
            Question::create([
                'question' => $thoughtRecordQuestion,
                'type' => QuestionType::OPEN->value,
                'record_type' => RecordType::THOUGHT_RECORD->value,
                'created_at' => now(),
            ]);
        }

        $emotionRecordQuestions = [
            'Tanggal/Jam',
            'Kejadian atau situasi',
            'Pemikiran yang muncul',
            'Emosi dan intensitas (1-10)',
            'Cara yang dilakukan',
            'Dampak pada emosi dan intensitas (1-10)',
        ];

        foreach ($emotionRecordQuestions as $emotionRecordQuestion) {
            Question::create([
                'question' => $emotionRecordQuestion,
                'type' => QuestionType::OPEN->value,
                'record_type' => RecordType::EMOTION_RECORD->value,
                'created_at' => now(),
            ]);
        }

        $committedActionQuestions = [
            'Area',
            'Tujuan',
            'Rencana',
            'Waktu pelaksanaan',
            'Terlaksana/Tidak Terlaksana',
            'Hambatan',
            'Cara mengatasi',
        ];

        foreach ($committedActionQuestions as $committedActionQuestion) {
            Question::create([
                'question' => $committedActionQuestion,
                'type' => QuestionType::OPEN->value,
                'record_type' => RecordType::COMMITTED_ACTION->value,
                'created_at' => now(),
            ]);
        }
    }
}
