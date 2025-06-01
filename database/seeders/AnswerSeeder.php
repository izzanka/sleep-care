<?php

namespace Database\Seeders;

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
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
        $therapyInProgress = Therapy::where('status', TherapyStatus::IN_PROGRESS->value)->first();
        $therapyCompleted = Therapy::where('status', TherapyStatus::COMPLETED->value)->first();

        foreach ([$therapyInProgress, $therapyCompleted] as $therapy) {
            $this->seedSleepDiaries($therapy);
            $this->seedIdentifyValue($therapy);
            $this->seedThoughtRecords($therapy);
            $this->seedEmotionRecords($therapy);
            $this->seedCommittedActions($therapy);
        }
    }

    private function seedSleepDiaries(Therapy $therapy)
    {
        for ($week = 1; $week <= 6; $week++) {
            for ($day = 1; $day <= 7; $day++) {
                $currentDate = $therapy->start_date->addDays((($week - 1) * 7) + ($day - 1));
                $sleepDiaryTimestamp = $currentDate->setTime(rand(8, 22), rand(0, 59));

                $sleepDiary = SleepDiary::create([
                    'therapy_id' => $therapy->id,
                    'week' => $week,
                    'day' => $day,
                    'date' => $currentDate->toDateString(),
                    'title' => 'Sleep Diary Minggu ke-'.$week,
                ]);

                $sleepDiaryQuestions = [
                    ['id' => 1, 'type' => QuestionType::BOOLEAN->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 2, 'type' => QuestionType::BOOLEAN->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 3, 'type' => QuestionType::NUMBER->value, 'answer' => 2, 'note' => 'Siang'],
                    ['id' => 4, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Siang'],
                    ['id' => 5, 'type' => QuestionType::BOOLEAN->value, 'answer' => 'Penenang', 'note' => 'Siang'],
                    ['id' => 6, 'type' => QuestionType::NUMBER->value, 'answer' => 1, 'note' => 'Siang'],
                    ['id' => 7, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Siang'],
                    ['id' => 8, 'type' => QuestionType::BOOLEAN->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 9, 'type' => QuestionType::BOOLEAN->value, 'answer' => false, 'note' => 'Siang'],
                    ['id' => 10, 'type' => QuestionType::BOOLEAN->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 11, 'type' => QuestionType::BOOLEAN->value, 'answer' => false, 'note' => 'Siang'],
                    ['id' => 12, 'type' => QuestionType::BOOLEAN->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 13, 'type' => QuestionType::BOOLEAN->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 14, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Malam'],
                    ['id' => 15, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Malam'],
                    ['id' => 16, 'type' => QuestionType::NUMBER->value, 'answer' => fake()->numberBetween(0, 10), 'note' => 'Malam'],
                    ['id' => 17, 'type' => QuestionType::NUMBER->value, 'answer' => fake()->numberBetween(0, 5), 'note' => 'Malam'],
                    ['id' => 18, 'type' => QuestionType::NUMBER->value, 'answer' => fake()->numberBetween(1, 5), 'note' => 'Malam'],
                    ['id' => 19, 'type' => QuestionType::BOOLEAN->value, 'answer' => false, 'note' => 'Malam'],
                ];

                $sleepDiaryRecords = [];
                foreach ($sleepDiaryQuestions as $question) {
                    $answerTimestamp = $sleepDiaryTimestamp->addMinutes(rand(1, 30));

                    $answer = Answer::create([
                        'type' => $question['type'],
                        'answer' => $question['answer'],
                        'note' => $question['note'],
                        'created_at' => $answerTimestamp,
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
    }

    private function seedIdentifyValue(Therapy $therapy)
    {
        $identifyValue = IdentifyValue::create([
            'therapy_id' => $therapy->id,
        ]);

        $questions = [
            ['id' => 20, 'type' => QuestionType::NUMBER->value],
            ['id' => 21, 'type' => QuestionType::TEXT->value],
            ['id' => 22, 'type' => QuestionType::NUMBER->value],
        ];

        $categories = [
            'Keluarga', 'Pernikahan/Relasi', 'Pertemanan', 'Pekerjaan/Karir', 'Pendidikan/Pengembangan Diri',
            'Rekreasi/Hiburan/Waktu Luang', 'Spiritualitas', 'Komunitas/Relawan', 'Lingkungan/Alam', 'Kesehatan',
        ];

        $realSentences = [
            'Keluarga' => [
                'Aku ingin jadi pribadi yang lebih peduli dan hadir untuk keluargaku.',
                'Aku ingin jadi pribadi yang mampu menjaga keharmonisan keluarga.',
                'Aku ingin jadi pribadi yang menjadi teladan bagi anak-anakku.',
            ],
            'Pernikahan/Relasi' => [
                'Aku ingin jadi pasangan yang sabar dan penuh pengertian.',
                'Aku ingin jadi pribadi yang setia dan menjaga komitmen.',
                'Aku ingin jadi pribadi yang selalu mendukung pasanganku.',
            ],
            'Pertemanan' => [
                'Aku ingin jadi teman yang setia dan bisa dipercaya.',
                'Aku ingin jadi pribadi yang menghargai perbedaan dalam pertemanan.',
                'Aku ingin jadi pribadi yang hadir saat dibutuhkan teman.',
            ],
            'Pekerjaan/Karir' => [
                'Aku ingin jadi pribadi yang profesional dan berdedikasi.',
                'Aku ingin jadi pribadi yang produktif dan bertanggung jawab.',
                'Aku ingin jadi pribadi yang terus berkembang dalam karier.',
            ],
            'Pendidikan/Pengembangan Diri' => [
                'Aku ingin jadi pribadi yang haus ilmu dan terus belajar.',
                'Aku ingin jadi pribadi yang disiplin dalam menuntut ilmu.',
                'Aku ingin jadi pribadi yang berbagi ilmu kepada orang lain.',
            ],
            'Rekreasi/Hiburan/Waktu Luang' => [
                'Aku ingin jadi pribadi yang tahu cara menikmati hidup.',
                'Aku ingin jadi pribadi yang seimbang antara kerja dan hiburan.',
                'Aku ingin jadi pribadi yang menghargai waktu untuk bersantai.',
            ],
            'Spiritualitas' => [
                'Aku ingin jadi pribadi yang dekat dengan Tuhan.',
                'Aku ingin jadi pribadi yang bersyukur dan penuh iman.',
                'Aku ingin jadi pribadi yang menjalani hidup sesuai nilai-nilai spiritual.',
            ],
            'Komunitas/Relawan' => [
                'Aku ingin jadi pribadi yang aktif dalam kegiatan sosial.',
                'Aku ingin jadi pribadi yang membawa dampak positif bagi masyarakat.',
                'Aku ingin jadi pribadi yang peduli terhadap sesama.',
            ],
            'Lingkungan/Alam' => [
                'Aku ingin jadi pribadi yang peduli lingkungan.',
                'Aku ingin jadi pribadi yang menjaga kebersihan dan alam.',
                'Aku ingin jadi pribadi yang mengurangi penggunaan plastik.',
            ],
            'Kesehatan' => [
                'Aku ingin jadi pribadi yang menjaga kesehatan fisik dan mental.',
                'Aku ingin jadi pribadi yang hidup sehat dan bugar.',
                'Aku ingin jadi pribadi yang sadar pentingnya pola makan dan olahraga.',
            ],
        ];

        $relations = [];

        foreach ($categories as $category) {
            foreach ($questions as $question) {
                $randomAnswer = match ($question['type']) {
                    QuestionType::NUMBER->value => fake()->numberBetween(1, 10),
                    QuestionType::TEXT->value => $realSentences[$category][array_rand($realSentences[$category])],
                    default => null,
                };

                $answer = Answer::create([
                    'type' => $question['type'],
                    'answer' => $randomAnswer,
                    'note' => $category,
                ]);

                $relations[] = [
                    'identify_value_id' => $identifyValue->id,
                    'question_id' => $question['id'],
                    'answer_id' => $answer->id,
                ];
            }
        }

        DB::table('identify_value_question_answer')->insert($relations);
    }

    private function seedThoughtRecords(Therapy $therapy)
    {
        $thoughtRecord = ThoughtRecord::create([
            'therapy_id' => $therapy->id,
        ]);

        $realEvents = [
            'Saya ditegur atasan karena terlambat datang ke kantor.',
            'Teman saya tidak membalas pesan saya sepanjang hari.',
            'Saya gagal dalam presentasi proyek di depan tim.',
            'Saya melihat mantan saya sedang bersama orang lain.',
            'Saya mendapat nilai buruk dalam ujian yang saya pikir bisa saya kerjakan.',
        ];

        $realThoughts = [
            'Saya merasa saya tidak cukup baik.',
            'Mungkin saya memang tidak penting bagi orang lain.',
            'Saya tidak akan pernah berhasil.',
            'Saya tidak layak dicintai.',
            'Saya tidak cukup pintar untuk berhasil.',
            'Saya selalu gagal dalam hal penting.',
            'Saya pasti akan dipecat.',
            'Semua orang pasti menilai saya buruk.',
            'Saya pasti akan sendiri selamanya.',
            'Saya selalu membuat kesalahan.',
            json_encode(['Saya selalu membuat gagal.', 'S'])
        ];

        for ($week = 0; $week < 6; $week++) {
            $recordsThisWeek = rand(0, 7);

            for ($i = 0; $i < $recordsThisWeek; $i++) {
                $event = $realEvents[array_rand($realEvents)];
                $thought = $realThoughts[array_rand($realThoughts)];
                $recordDate = $therapy->start_date->addWeeks($week)->addDays(rand(0, 6));
                $recordTime = $recordDate->setTime(rand(8, 20), rand(0, 59));

                $questions = [
                    ['id' => 23, 'type' => QuestionType::DATE->value, 'answer' => $recordDate->toDateString()],
                    ['id' => 24, 'type' => QuestionType::TIME->value, 'answer' => $recordTime->format('H:i')],
                    ['id' => 25, 'type' => QuestionType::TEXT->value, 'answer' => $event],
                    ['id' => 26, 'type' => QuestionType::TEXT->value, 'answer' => $thought],
                ];

                $pivotData = [];
                foreach ($questions as $question) {
                    $answer = Answer::create([
                        'type' => $question['type'],
                        'answer' => $question['answer'],
                        'created_at' => $recordTime,
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
    }

    private function seedEmotionRecords(Therapy $therapy)
    {
        $emotions = ['Bahagia', 'Sedih', 'Marah', 'Takut', 'Jijik', 'Terkejut'];
        $realEvents = [
            'Saya dimarahi atasan di depan rekan kerja.',
            'Saya menerima kabar duka dari keluarga.',
            'Saya mendapatkan kabar baik tentang kelulusan saya.',
            'Saya mengalami kemacetan parah saat perjalanan ke kantor.',
            'Saya bertengkar dengan pasangan saya pagi ini.',
        ];

        $realThoughts = [
            'Saya merasa tidak berguna.',
            'Saya takut hal buruk akan terjadi.',
            'Mungkin saya memang tidak cukup baik.',
            'Saya sangat senang dan bersyukur.',
            'Saya merasa kesepian dan tidak dipedulikan.',
        ];

        $copingStrategies = [
            'Saya mencoba menarik napas dalam dan menenangkan diri.',
            'Saya menelepon teman untuk bercerita.',
            'Saya menulis perasaan saya di jurnal.',
            'Saya mendengarkan musik yang menenangkan.',
            'Saya berjalan-jalan sebentar untuk meredakan emosi.',
        ];

        $emotionRecord = EmotionRecord::create([
            'therapy_id' => $therapy->id,
        ]);

        for ($week = 0; $week < 6; $week++) {
            $recordsThisWeek = rand(0, 7);

            for ($i = 0; $i < $recordsThisWeek; $i++) {
                $recordDate = $therapy->start_date->addWeeks($week)->addDays(rand(0, 6));
                $recordTime = $recordDate->setTime(rand(8, 20), rand(0, 59));

                $emotionQuestions = [
                    ['id' => 27, 'type' => QuestionType::DATE->value, 'answer' => $recordDate->toDateString()],
                    ['id' => 28, 'type' => QuestionType::TIME->value, 'answer' => $recordTime->format('H:i')],
                    ['id' => 29, 'type' => QuestionType::TEXT->value, 'answer' => $realEvents[array_rand($realEvents)]],
                    ['id' => 30, 'type' => QuestionType::TEXT->value, 'answer' => $realThoughts[array_rand($realThoughts)]],
                    ['id' => 31, 'type' => QuestionType::TEXT->value, 'answer' => $emotions[array_rand($emotions)]],
                    ['id' => 32, 'type' => QuestionType::NUMBER->value, 'answer' => rand(6, 10)],
                    ['id' => 33, 'type' => QuestionType::TEXT->value, 'answer' => $copingStrategies[array_rand($copingStrategies)]],
                    ['id' => 34, 'type' => QuestionType::NUMBER->value, 'answer' => rand(1, 5)],
                ];

                $emotionRecords = [];
                foreach ($emotionQuestions as $question) {
                    $answer = Answer::create([
                        'type' => $question['type'],
                        'answer' => $question['answer'],
                        'created_at' => $recordTime,
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
    }

    private function seedCommittedActions(Therapy $therapy)
    {
        $categories = [
            'Keluarga', 'Pernikahan/Relasi', 'Pertemanan', 'Pekerjaan/Karir', 'Pendidikan/Pengembangan Diri',
            'Rekreasi/Hiburan/Waktu Luang', 'Spiritualitas', 'Komunitas/Relawan', 'Lingkungan/Alam', 'Kesehatan',
        ];

        $goals = [
            'Menghabiskan waktu berkualitas dengan keluarga.',
            'Meningkatkan komunikasi dengan pasangan.',
            'Menjalin kembali hubungan dengan teman lama.',
            'Menyelesaikan tugas kerja tepat waktu.',
            'Belajar topik baru untuk pengembangan diri.',
            'Melakukan aktivitas menyenangkan di akhir pekan.',
            'Melakukan meditasi secara rutin.',
            'Berpartisipasi dalam kegiatan sosial.',
            'Membersihkan lingkungan sekitar rumah.',
            'Menjaga pola makan sehat.',
        ];

        $plans = [
            'Merencanakan piknik keluarga di akhir pekan.',
            'Mengatur jadwal ngobrol santai dengan pasangan setiap malam.',
            'Menghubungi teman lama melalui pesan singkat.',
            'Membuat to-do list harian dan menyelesaikannya.',
            'Mengikuti kursus online selama 1 minggu.',
            'Menonton film favorit bersama teman.',
            'Meluangkan 10 menit untuk meditasi setiap pagi.',
            'Bergabung dalam kegiatan gotong royong RT.',
            'Menanam pohon di halaman rumah.',
            'Memasak makanan sehat di rumah setiap hari.',
        ];

        $barriers = [
            'Waktu yang terbatas.',
            'Perasaan malas atau tidak termotivasi.',
            'Cuaca yang tidak mendukung.',
            'Konflik dengan orang lain.',
            'Terlalu banyak pekerjaan lain.',
        ];

        $solutions = [
            'Mengatur waktu lebih baik dengan membuat jadwal.',
            'Memotivasi diri dengan mengingat manfaat kegiatan.',
            'Mencari alternatif kegiatan dalam ruangan.',
            'Bicara dan mencari solusi bersama orang terkait.',
            'Membagi tugas agar lebih ringan.',
        ];

        $committedAction = CommittedAction::create(['therapy_id' => $therapy->id]);

        foreach ($categories as $index => $category) {
            $committedQuestions = [
                ['id' => 35, 'type' => QuestionType::TEXT->value, 'answer' => $category],
                ['id' => 36, 'type' => QuestionType::TEXT->value, 'answer' => $goals[$index]],
                ['id' => 37, 'type' => QuestionType::TEXT->value, 'answer' => $plans[$index]],
                ['id' => 38, 'type' => QuestionType::TEXT->value, 'answer' => 'Hari Sabtu sore pukul 16:00'],
                ['id' => 39, 'type' => QuestionType::BOOLEAN->value, 'answer' => rand(0, 1)],
                ['id' => 40, 'type' => QuestionType::TEXT->value, 'answer' => $barriers[array_rand($barriers)]],
                ['id' => 41, 'type' => QuestionType::TEXT->value, 'answer' => $solutions[array_rand($solutions)]],
            ];

            $committedRecords = [];
            foreach ($committedQuestions as $question) {
                $answer = Answer::create([
                    'type' => $question['type'],
                    'answer' => $question['answer'],
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
