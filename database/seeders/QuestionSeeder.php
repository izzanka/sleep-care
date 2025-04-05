<?php

namespace Database\Seeders;

use App\Enum\QuestionType;
use App\Enum\RecordType;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        $sleepDiaryTitleParents = [
            'Apakah kamu tidur siang?',
            'Apakah kamu mengkonsumsi obat tidur?',
        ];

        $parentQuestions = [];

        foreach ($sleepDiaryTitleParents as $sleepDiaryTitleParent) {
            $parentQuestions[] = Question::create([
                'question' => $sleepDiaryTitleParent,
                'type' => QuestionType::BINARY->value,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'note' => 'Siang',
                'is_parent' => true,
                'parent_id' => null,
                'created_at' => $timestamp,
            ]);
        }

        $firstParent = $parentQuestions[0] ?? null;
        $secondParent = $parentQuestions[1] ?? null;

        $firstSleepDiaryTitleChilds = [
            'Berapa lama? (dalam menit)' => QuestionType::NUMBER->value,
            'Pukul berapa?' => QuestionType::TIME->value,
        ];

        $questions = [];

        if ($firstParent) {
            foreach ($firstSleepDiaryTitleChilds as $question => $type) {
                $questions[] = [
                    'question' => $question,
                    'type' => $type,
                    'record_type' => RecordType::SLEEP_DIARY->value,
                    'parent_id' => $firstParent->id,
                    'is_parent' => false,
                    'note' => 'Siang',
                    'created_at' => $timestamp,
                ];
            }
        }

        $secondSleepDiaryTitleChilds = [
            'Apa jenis obatnya?' => QuestionType::TEXT->value,
            'Berapa banyak?' => QuestionType::NUMBER->value,
            'Pukul berapa?' => QuestionType::TIME->value,
        ];

        if ($secondParent) {
            foreach ($secondSleepDiaryTitleChilds as $question => $type) {
                $questions[] = [
                    'question' => $question,
                    'type' => $type,
                    'record_type' => RecordType::SLEEP_DIARY->value,
                    'parent_id' => $secondParent->id,
                    'is_parent' => false,
                    'note' => 'Siang',
                    'created_at' => $timestamp,
                ];
            }
        }

        $sleepDiaryBinaryQuestions = [
            'Apakah kamu mengkonsumsi kafein (contoh: kopi, teh, soda, coklat, minuman berenergi) setelah pukul 18.00?',
            'Apakah kamu mengkonsumsi alkohol setelah pukul 18.00?',
            'Apakah kamu menggunakan nikotin (contoh: rokok) setelah pukul 18.00?',
            'Apakah kamu berolahraga?',
            'Apakah kamu mengkonsumsi makanan berat atau snack setelah pukul 18.00?',
            'Apakah kamu mengantuk sepanjang hari?',
        ];

        foreach ($sleepDiaryBinaryQuestions as $question) {
            $questions[] = [
                'question' => $question,
                'type' => QuestionType::BINARY->value,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'note' => 'Siang',
                'parent_id' => null,
                'is_parent' => false,
                'created_at' => $timestamp,
            ];
        }

        $sleepDiaryOpenQuestions = [
            'Pukul berapa kamu mulai mematikan lampu untuk mulai tidur?' => QuestionType::TIME->value,
            'Pukul berapa kamu bangun tidur?' => QuestionType::TIME->value,
            'Berapa total jam kamu tidur? (dalam jam)' => QuestionType::NUMBER->value,
            'Berapa kali kamu terbangun di malam hari?' => QuestionType::NUMBER->value,
            'Isilah skala kualitas tidurmu (dalam skala 1-5, 1 sangat tidak berkualitas, 5 sangat berkualitas)' => QuestionType::NUMBER->value,
            'Apakah kamu merasa tidurmu cukup?' => QuestionType::TEXT->value,
        ];

        foreach ($sleepDiaryOpenQuestions as $question => $type) {
            $questions[] = [
                'question' => $question,
                'type' => $type,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'note' => 'Malam',
                'parent_id' => null,
                'is_parent' => false,
                'created_at' => $timestamp,
            ];
        }

        $identifyValueTitleParents = [
            'Skala Kepentingan, Seberapa penting area ini untuk Anda? Skala 1 - 10 (1 = Sangat tidak penting, 10 = Sangat penting)' => QuestionType::NUMBER->value,
            'Aku ingin jadi pribadi yang, Contoh: Pengisian di area karir: aku ingin jadi orang yang menguasai bidang pekerjaan' => QuestionType::TEXT->value,
            'Skor Kesuaian, Seberapa sesuai kondisi Anda saat ini dengan pribadi yang Anda inginkan? Skala 1 - 10 (1 = Sangat tidak sesuai, 10 = Sangat sesuai)' => QuestionType::NUMBER->value,
        ];

        foreach ($identifyValueTitleParents as $question => $type) {
            $questions[] = [
                'question' => $question,
                'type' => $type,
                'record_type' => RecordType::IDENTIFY_VALUE->value,
                'note' => null,
                'is_parent' => true,
                'parent_id' => null,
                'created_at' => $timestamp,
            ];
        }

        $thoughtRecordQuestions = [
            'Tanggal dan Jam' => QuestionType::DATE_TIME->value,
            'Kejadian atau situasi' => QuestionType::TEXT->value,
            'Pemikiran yang muncul' => QuestionType::TEXT->value,
        ];

        foreach ($thoughtRecordQuestions as $question => $type) {
            $questions[] = [
                'question' => $question,
                'type' => $type,
                'record_type' => RecordType::THOUGHT_RECORD->value,
                'note' => null,
                'parent_id' => null,
                'is_parent' => false,
                'created_at' => $timestamp,
            ];
        }

        $emotionRecordQuestions = [
            'Tanggal dan Jam' => QuestionType::DATE_TIME->value,
            'Kejadian atau situasi' => QuestionType::TEXT->value,
            'Pemikiran yang muncul' => QuestionType::TEXT->value,
            'Emosi dan intensitas (1-10)' => QuestionType::TEXT->value,
            'Cara yang dilakukan' => QuestionType::TEXT->value,
            'Dampak pada emosi dan intensitas (1-10)' => QuestionType::TEXT->value,
        ];

        foreach ($emotionRecordQuestions as $question => $type) {
            $questions[] = [
                'question' => $question,
                'type' => $type,
                'record_type' => RecordType::EMOTION_RECORD->value,
                'note' => null,
                'parent_id' => null,
                'is_parent' => false,
                'created_at' => $timestamp,
            ];
        }

        $committedActionQuestions = [
            'Area' => QuestionType::TEXT->value,
            'Tujuan' => QuestionType::TEXT->value,
            'Rencana' => QuestionType::TEXT->value,
            'Waktu pelaksanaan' => QuestionType::TEXT->value,
            'Terlaksana/Tidak Terlaksana' => QuestionType::BINARY->value,
            'Hambatan' => QuestionType::TEXT->value,
            'Cara mengatasi' => QuestionType::TEXT->value,
        ];

        foreach ($committedActionQuestions as $question => $type) {
            $questions[] = [
                'question' => $question,
                'type' => $type,
                'record_type' => RecordType::COMMITTED_ACTION->value,
                'note' => null,
                'parent_id' => null,
                'is_parent' => false,
                'created_at' => $timestamp,
            ];
        }

        Question::insert($questions);
    }
}
