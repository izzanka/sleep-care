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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnswerSeeder extends Seeder
{
    public function run(): void
    {
        $therapy = Therapy::select('id', 'start_date')->first();
        $timestamp = now();

        for ($week = 1; $week <= 6; $week++) {
            for ($day = 1; $day <= 7; $day++) {
                $currentDate = $therapy->start_date->copy()->addDays((($week - 1) * 7) + ($day - 1));

                $sleepDiary = SleepDiary::create([
                    'therapy_id' => $therapy->id,
                    'week' => $week,
                    'day' => $day,
                    'date' => $currentDate->toDateString(),
                    'title' => 'Sleep Diary Minggu ke-'.$week,
                    'created_at' => $timestamp,
                ]);

                $sleepDiaryQuestions = [
                    ['id' => 1, 'type' => QuestionType::BINARY->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 2, 'type' => QuestionType::BINARY->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 3, 'type' => QuestionType::NUMBER->value, 'answer' => 2, 'note' => 'Siang'],
                    ['id' => 4, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Siang'],
                    ['id' => 5, 'type' => QuestionType::BINARY->value, 'answer' => 'Penenang', 'note' => 'Siang'],
                    ['id' => 6, 'type' => QuestionType::NUMBER->value, 'answer' => 1, 'note' => 'Siang'],
                    ['id' => 7, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Siang'],
                    ['id' => 8, 'type' => QuestionType::BINARY->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 9, 'type' => QuestionType::BINARY->value, 'answer' => false, 'note' => 'Siang'],
                    ['id' => 10, 'type' => QuestionType::BINARY->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 11, 'type' => QuestionType::BINARY->value, 'answer' => false, 'note' => 'Siang'],
                    ['id' => 12, 'type' => QuestionType::BINARY->value, 'answer' => true, 'note' => 'Siang'],
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
                        'note' => $question['note'],
                        'created_at' => $timestamp,
                    ]);

                    $sleepDiaryRecords[] = [
                        'sleep_diary_id' => $sleepDiary->id,
                        'question_id' => $question['id'],
                        'answer_id' => $answer->id,
                    ];
                }

                DB::table('sleep_diary_question_answer')->insert($sleepDiaryRecords);
            }
        }

        $identifyValue = IdentifyValue::create([
            'therapy_id' => $therapy->id,
            'created_at' => $timestamp,
        ]);

        $questions = [
            ['id' => 20, 'type' => QuestionType::NUMBER->value],
            ['id' => 21, 'type' => QuestionType::TEXT->value],
            ['id' => 22, 'type' => QuestionType::NUMBER->value],
        ];

        $categories = ['Keluarga', 'Pernikahan', 'Pertemanan', 'Pekerjaan', 'Pendidikan',
            'Rekreasi', 'Spiritualitas', 'Komunitas', 'Lingkungan', 'Kesehatan'];

        $relations = [];

        foreach ($categories as $category) {
            foreach ($questions as $question) {
                $randomAnswer = match ($question['type']) {
                    QuestionType::NUMBER->value => fake()->numberBetween(1, 10),
                    QuestionType::TEXT->value => fake()->sentence(),
                    default => null,
                };

                $answer = Answer::create([
                    'type' => $question['type'],
                    'answer' => $randomAnswer,
                    'note' => $category,
                    'created_at' => $timestamp,
                ]);

                $relations[] = [
                    'identify_value_id' => $identifyValue->id,
                    'question_id' => $question['id'],
                    'answer_id' => $answer->id,
                ];
            }
        }

        DB::table('identify_value_question_answer')->insert($relations);

        // Thought Record
        $thoughtRecord = ThoughtRecord::create([
            'therapy_id' => $therapy->id,
            'created_at' => now(),
        ]);

        for ($week = 0; $week < 6; $week++) {
            $recordsThisWeek = rand(0, 7);

            for ($i = 0; $i < $recordsThisWeek; $i++) {
                $isJsonAnswer = rand(0, 1) === 1;
                $answer25 = $isJsonAnswer
                    ? json_encode([fake()->sentence(), fake()->sentence()])
                    : fake()->sentence();

                $questions = [
                    ['id' => 23, 'type' => QuestionType::DATE->value, 'answer' => $therapy->start_date->addWeeks($week)->toDateString()],
                    ['id' => 24, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i')],
                    ['id' => 25, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
                    ['id' => 26, 'type' => QuestionType::TEXT->value, 'answer' => $answer25],
                ];

                $pivotData = [];

                foreach ($questions as $question) {
                    $answer = Answer::create([
                        'type' => $question['type'],
                        'answer' => $question['answer'],
                        'created_at' => $timestamp,
                    ]);

                    $pivotData[] = [
                        'thought_record_id' => $thoughtRecord->id,
                        'question_id' => $question['id'],
                        'answer_id' => $answer->id,
                    ];
                }

                DB::table('thought_record_question_answer')->insert($pivotData);
            }
        }

        // Emotion Record
        $positive = ['Bahagia', 'Gembira', 'Syukur', 'Tenang', 'Bangga'];
        $negative = ['Cemas', 'Malu', 'Frustasi', 'Bingung', 'Kecewa'];

        $emotionRecord = EmotionRecord::create(['therapy_id' => $therapy->id, 'created_at' => $timestamp]);

        for ($week = 0; $week < 6; $week++) {
            $recordsThisWeek = rand(0, 7);

            for ($i = 0; $i < $recordsThisWeek; $i++) {
                $emotion31 = rand(0, 1) === 1 ? $positive[0] : $negative[0];

                $emotionQuestions = [
                    ['id' => 27, 'type' => QuestionType::DATE->value, 'answer' => $therapy->start_date->addWeeks($week)->toDateString()],
                    ['id' => 28, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i')],
                    ['id' => 29, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
                    ['id' => 30, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
                    ['id' => 31, 'type' => QuestionType::TEXT->value, 'answer' => $emotion31],
                    ['id' => 32, 'type' => QuestionType::NUMBER->value, 'answer' => rand(1, 10)],
                    ['id' => 33, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
                    ['id' => 34, 'type' => QuestionType::NUMBER->value, 'answer' => rand(1, 10)],
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
                    ];
                }

                DB::table('emotion_record_question_answer')->insert($emotionRecords);
            }
        }

        // Committed Action
        $committedAction = CommittedAction::create(['therapy_id' => $therapy->id]);

        for ($week = 0; $week < 6; $week++) {
            $recordsThisWeek = rand(0, 7);

            for ($i = 0; $i < $recordsThisWeek; $i++) {
                $committedQuestions = [
                    ['id' => 35, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
                    ['id' => 36, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
                    ['id' => 37, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
                    ['id' => 38, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
                    ['id' => 39, 'type' => QuestionType::BINARY->value, 'answer' => rand(0, 1)],
                    ['id' => 40, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
                    ['id' => 41, 'type' => QuestionType::TEXT->value, 'answer' => fake()->sentence()],
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
                    ];
                }
                DB::table('committed_action_question_answer')->insert($committedRecords);
            }
        }
    }
}
