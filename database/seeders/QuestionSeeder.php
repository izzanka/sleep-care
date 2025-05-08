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
                'created_at' => now(),
            ]);
        }

        $firstParent = $parentQuestions[0] ?? null;
        $secondParent = $parentQuestions[1] ?? null;

        $firstSleepDiaryTitleChilds = [
            'Berapa lama? (dalam jam)' => QuestionType::NUMBER->value,
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
                    'created_at' => now(),
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
                    'created_at' => now(),
                ];
            }
        }

        $sleepDiaryBinaryQuestions = [
            'Apakah kamu berolahraga?',
            'Apakah kamu mengantuk sepanjang hari?',
            'Apakah kamu mengkonsumsi kafein setelah pukul 18.00?',
            'Apakah kamu mengkonsumsi alkohol setelah pukul 18.00?',
            'Apakah kamu menggunakan nikotin setelah pukul 18.00?',
            'Apakah kamu mengkonsumsi makanan setelah pukul 18.00?',
        ];

        foreach ($sleepDiaryBinaryQuestions as $question) {
            $questions[] = [
                'question' => $question,
                'type' => QuestionType::BINARY->value,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'note' => 'Siang',
                'parent_id' => null,
                'is_parent' => false,
                'created_at' => now(),
            ];
        }

        $sleepDiaryOpenQuestions = [
            'Pukul berapa kamu mulai mematikan lampu untuk mulai tidur?' => QuestionType::TIME->value,
            'Pukul berapa kamu bangun tidur?' => QuestionType::TIME->value,
            'Berapa total jam kamu tidur? (dalam jam)' => QuestionType::NUMBER->value,
            'Berapa kali kamu terbangun di malam hari?' => QuestionType::NUMBER->value,
            'Isilah skala kualitas tidurmu? (skala 1-5)' => QuestionType::NUMBER->value,
            'Apakah kamu merasa tidurmu cukup?' => QuestionType::BINARY->value,
        ];

        foreach ($sleepDiaryOpenQuestions as $question => $type) {
            $questions[] = [
                'question' => $question,
                'type' => $type,
                'record_type' => RecordType::SLEEP_DIARY->value,
                'note' => 'Malam',
                'parent_id' => null,
                'is_parent' => false,
                'created_at' => now(),
            ];
        }

        $identifyValueTitleParents = [
            'Skala Kepentingan, seberapa penting area ini untuk anda? (skala 1-10)' => QuestionType::NUMBER->value,
            'Aku ingin jadi pribadi yang?' => QuestionType::TEXT->value,
            'Skor Kesesuaian, seberapa sesuai kondisi anda saat ini dengan pribadi yang Anda inginkan? (skala 1-10)' => QuestionType::NUMBER->value,
        ];

        foreach ($identifyValueTitleParents as $question => $type) {
            $questions[] = [
                'question' => $question,
                'type' => $type,
                'record_type' => RecordType::IDENTIFY_VALUE->value,
                'note' => null,
                'is_parent' => true,
                'parent_id' => null,
                'created_at' => now(),
            ];
        }

        $thoughtRecordQuestions = [
            'Tanggal' => QuestionType::DATE->value,
            'Jam' => QuestionType::TIME->value,
            'Kejadian' => QuestionType::TEXT->value,
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
                'created_at' => now(),
            ];
        }

        $emotionRecordQuestions = [
            'Tanggal' => QuestionType::DATE->value,
            'Jam' => QuestionType::TIME->value,
            'Kejadian' => QuestionType::TEXT->value,
            'Pemikiran yang muncul' => QuestionType::TEXT->value,
            'Emosi yang dirasakan' => QuestionType::TEXT->value,
            'Intensitas emosi sebelum diatasi (1-10)' => QuestionType::NUMBER->value,
            'Cara mengatasi' => QuestionType::TEXT->value,
            'Intensitas emosi setelah diatasi (1-10)' => QuestionType::NUMBER->value,
        ];

        foreach ($emotionRecordQuestions as $question => $type) {
            $questions[] = [
                'question' => $question,
                'type' => $type,
                'record_type' => RecordType::EMOTION_RECORD->value,
                'note' => null,
                'parent_id' => null,
                'is_parent' => false,
                'created_at' => now(),
            ];
        }

        $committedActionQuestions = [
            'Area' => QuestionType::TEXT->value,
            'Tujuan' => QuestionType::TEXT->value,
            'Rencana' => QuestionType::TEXT->value,
            'Waktu pelaksanaan' => QuestionType::TEXT->value,
            'Terlaksana' => QuestionType::BINARY->value,
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
                'created_at' => now(),
            ];
        }

        Question::insert($questions);
    }
}
