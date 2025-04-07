<?php

namespace Database\Seeders;

use App\Enum\QuestionType;
use App\Models\Answer;
use App\Models\CommittedAction;
use App\Models\EmotionRecord;
use App\Models\IdentifyValue;
use App\Models\SleepDiary;
use App\Models\Therapy;
use App\Models\ThoughtRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $therapy = Therapy::select('id')->first();
        $timestamp = now();

        $identifyValue = IdentifyValue::create([
            'therapy_id' => $therapy->id,
            'created_at' => $timestamp,
        ]);

        $questions = [
            ['id' => 20, 'type' => QuestionType::NUMBER->value, 'answer' => 4],
            ['id' => 21, 'type' => QuestionType::TEXT->value, 'answer' => fake()->realTextBetween(25, 100)],
            ['id' => 22, 'type' => QuestionType::NUMBER->value, 'answer' => 9],
        ];

        $categories = ['Keluarga', 'Pernikahan', 'Pertemanan', 'Pekerjaan', 'Pendidikan',
            'Rekreasi', 'Spiritualitas', 'Komunitas', 'Lingkungan', 'Kesehatan'];

        $relations = [];

        foreach ($categories as $category) {
            foreach ($questions as $question) {
                $answer = Answer::create([
                    'type' => $question['type'],
                    'answer' => $question['answer'],
                    'note' => $category,
                    'created_at' => $timestamp,
                ]);

                $relations[] = [
                    'identify_value_id' => $identifyValue->id,
                    'question_id' => $question['id'],
                    'answer_id' => $answer->id,
                    'created_at' => $timestamp,
                ];
            }
        }

        DB::table('identify_value_question_answer')->insert($relations);

        $thoughtRecord = ThoughtRecord::create(['therapy_id' => $therapy->id]);

        $thoughtQuestions1 = [
            ['id' => 23, 'type' => QuestionType::DATE->value, 'answer' => fake()->date()],
            ['id' => 24, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i')],
            ['id' => 25, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text().'1'],
            ['id' => 26, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text().'1'],
        ];

        $thoughtRecords1 = [];

        foreach ($thoughtQuestions1 as $question) {
            $answer = Answer::create([
                'type' => $question['type'],
                'answer' => $question['answer'],
                'created_at' => $timestamp,
            ]);

            $thoughtRecords1[] = [
                'thought_record_id' => $thoughtRecord->id,
                'question_id' => $question['id'],
                'answer_id' => $answer->id,
                'created_at' => $timestamp,
            ];
        }

        DB::table('thought_record_question_answer')->insert($thoughtRecords1);

        $thoughtQuestions2 = [
            ['id' => 23, 'type' => QuestionType::DATE->value, 'answer' => fake()->date()],
            ['id' => 24, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i')],
            ['id' => 25, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text().'2'],
            ['id' => 26, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text().'2'],
        ];

        $thoughtRecords2 = [];

        foreach ($thoughtQuestions2 as $question) {
            $answer = Answer::create([
                'type' => $question['type'],
                'answer' => $question['answer'],
                'created_at' => $timestamp,
            ]);

            $thoughtRecords2[] = [
                'thought_record_id' => $thoughtRecord->id,
                'question_id' => $question['id'],
                'answer_id' => $answer->id,
                'created_at' => $timestamp,
            ];
        }

        DB::table('thought_record_question_answer')->insert($thoughtRecords2);

        $emotionRecord = EmotionRecord::create(['therapy_id' => $therapy->id]);

        $emotionQuestions = [
            ['id' => 26, 'type' => QuestionType::DATE_TIME->value, 'answer' => fake()->dateTime()],
            ['id' => 27, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 28, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 29, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 30, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 31, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
        ];

        $emotionRecords = [];

        foreach ($emotionQuestions as $question) {
            $answer = Answer::create([
                'type' => $question['type'],
                'answer' => $question['answer'],
                'created_at' => $timestamp,
            ]);

            $emotionRecords[] = [
                'emotion_record_id' => $emotionRecord->id,
                'question_id' => $question['id'],
                'answer_id' => $answer->id,
                'created_at' => $timestamp,
            ];
        }

        DB::table('emotion_record_question_answer')->insert($emotionRecords);

        $committedAction = CommittedAction::create(['therapy_id' => $therapy->id]);

        $committedQuestions = [
            ['id' => 32, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 33, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 34, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 35, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 36, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 37, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
            ['id' => 38, 'type' => QuestionType::TEXT->value, 'answer' => fake()->text()],
        ];

        $committedRecords = [];

        foreach ($committedQuestions as $question) {
            $answer = Answer::create([
                'type' => $question['type'],
                'answer' => $question['answer'],
                'created_at' => $timestamp,
            ]);

            $committedRecords[] = [
                'committed_action_id' => $committedAction->id,
                'question_id' => $question['id'],
                'answer_id' => $answer->id,
                'created_at' => $timestamp,
            ];
        }

        DB::table('committed_action_question_answer')->insert($committedRecords);

        $startDate = Carbon::now()->startOfWeek();

        for ($week = 1; $week <= 6; $week++) {
            for ($day = 1; $day <= 7; $day++) {
                $currentDate = (clone $startDate)->addDays((($week - 1) * 7) + ($day - 1));
                $timestamp = now();

                $sleepDiary = SleepDiary::create([
                    'therapy_id' => $therapy->id,
                    'week' => $week,
                    'day' => $day,
                    'date' => $currentDate->toDateString(),
                    'title' => 'Sleep Diary Minggu ke-'.$week,
                ]);

                $sleepDiaryQuestions = [
                    ['id' => 1, 'type' => QuestionType::BINARY->value, 'answer' => false, 'note' => 'Siang'],
                    ['id' => 8, 'type' => QuestionType::BINARY->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 9, 'type' => QuestionType::BINARY->value, 'answer' => false, 'note' => 'Siang'],
                    ['id' => 10, 'type' => QuestionType::BINARY->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 11, 'type' => QuestionType::BINARY->value, 'answer' => false, 'note' => 'Siang'],
                    ['id' => 12, 'type' => QuestionType::BINARY->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 2, 'type' => QuestionType::BINARY->value, 'answer' => false, 'note' => 'Siang'],
                    ['id' => 13, 'type' => QuestionType::BINARY->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 14, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Malam'],
                    ['id' => 15, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Malam'],
                    ['id' => 16, 'type' => QuestionType::NUMBER->value, 'answer' => fake()->numberBetween(0, 10), 'note' => 'Malam'],
                    ['id' => 17, 'type' => QuestionType::NUMBER->value, 'answer' => fake()->numberBetween(0, 5), 'note' => 'Malam'],
                    ['id' => 18, 'type' => QuestionType::NUMBER->value, 'answer' => fake()->numberBetween(1, 5), 'note' => 'Malam'],
                    ['id' => 19, 'type' => QuestionType::BINARY->value, 'answer' => false, 'note' => 'Malam'],
                ];

                $sleepDiaryRecords = [];

                foreach ($sleepDiaryQuestions as $question) {
                    $answer = Answer::create([
                        'type' => $question['type'],
                        'answer' => $question['answer'],
                        'created_at' => $timestamp,
                        'note' => $question['note'],
                    ]);

                    $sleepDiaryRecords[] = [
                        'sleep_diary_id' => $sleepDiary->id,
                        'question_id' => $question['id'],
                        'answer_id' => $answer->id,
                        'created_at' => $timestamp,
                    ];
                }

                DB::table('sleep_diary_question_answer')->insert($sleepDiaryRecords);
            }
        }
    }
}
