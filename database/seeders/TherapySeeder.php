<?php

namespace Database\Seeders;

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Enum\UserRole;
use App\Models\Answer;
use App\Models\Chat;
use App\Models\CommittedAction;
use App\Models\Doctor;
use App\Models\EmotionRecord;
use App\Models\IdentifyValue;
use App\Models\Order;
use App\Models\SleepDiary;
use App\Models\Therapy;
use App\Models\TherapySchedule;
use App\Models\ThoughtRecord;
use App\Models\User;
use App\Notifications\OrderedTherapy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TherapySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctor = Doctor::whereHas('user', function ($query) {
            $query->where('name', 'psikolog');
        })->first();

        $doctor->user->is_therapy_in_progress = true;
        $doctor->user->save();

        $therapyScheduleDescriptions = [
            [
                'Psikolog dan pasien saling memperkenalkan diri',
                'Psikolog memberikan penjelasan mengenai gambaran terapi, insomnia, sleep hygiene, dan ACT.',
                'Psikolog menyusun kesepakatan bersama dengan pasien',
                'Psikolog meminta pasien untuk menyampaikan masalah insomnia yang dialami.',
                'Psikolog meminta pasien untuk mengisi catatan tidur (sleep diary) setiap hari selama terapi berlangsung.',
            ],
            [
                'Psikolog meminta pasien untuk mengenali dan mengeksplorasi nilai - nilai (value) pribadi',
                'Psikolog meminta pasien untuk mengidentifikasi value yang terdampak dengan adanya insomnia',
                'Psikolog mengajarkan teknik pernapasan kepada pasien.',
                'Psikolog meminta pasien untuk mengisi catatan identifikasi nilai (identify value).',
            ],
            [
                'Psikolog menjelaskan bentuk-bentuk pikiran yang mengganggu.',
                'Psikolog meminta pasien untuk mengidentifikasi pikiran menggangu yang menghambat tidur',
                'Psikolog meminta pasien untuk mempraktikkan cara-cara mengatasi pikiran yang mengganggu',
                'Psikolog meminta pasien untuk melakukan latihan observasi diri sebagai konteks (self-as-context).',
                'Psikolog meminta pasien untuk mengisi catatan pikiran (thought record) dan catatan emosi (emotion record).',
            ],
            [
                'Psikolog menjelaskan konsep penerimaan (acceptance)',
                'Psikolog menjelaskan penerimaan mindfulness di kehidupan sehari-hari',
                'Psikolog meminta pasien untuk mempraktikkan teknik acceptance dan mindfulness.',
            ],
            [
                'Psikolog meminta pasien mengevaluasi kesesuaian tindakan dengan value yang dimiliki',
                'Psikolog menjelaskan konsep tindakan berkomitmen (committed action).',
                'Psikolog meminta pasien untuk mengisi catatan tindakan berkomitmen (committed action) sesuai value yang dimiliki',
            ],
            [
                'Psikolog meminta pasien mengevaluasi tindakan berkomitmen yang telah dilaksanakan, serta menganalisis hambatan dan cara mengatasinya',
                'Psikolog meminta pasien untuk merangkum keseluruhan sesi terapi dan memberikan ulasan terhadap psikolog dan sesi terapi.',
            ],
        ];

        // Create 2 IN_PROGRESS therapies
        for ($i = 0; $i < 2; $i++) {
            $patient = User::where('role', UserRole::PATIENT->value)->inRandomOrder()->first();
            $patient->is_therapy_in_progress = true;
            $patient->save();

            $therapy = Therapy::factory()->create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'status' => TherapyStatus::IN_PROGRESS->value,
            ]);

            Order::factory()->create(['therapy_id' => $therapy->id]);

            $this->seedSleepDiaries($therapy);
            $this->seedIdentifyValue($therapy);
            $this->seedThoughtRecords($therapy);
            $this->seedEmotionRecords($therapy);
            $this->seedCommittedActions($therapy);

            for ($j = 1; $j < 3; $j++) {
                Chat::create([
                    'therapy_id' => $therapy->id,
                    'sender_id' => $patient->id,
                    'receiver_id' => $doctor->user->id,
                    'message' => match ($j) {
                        1 => 'Halo Dok, saya ingin menanyakan tentang jadwal sesi terapi kedua saya.',
                        2 => 'Apakah sudah ada kepastian tanggal dan jamnya?',
                    },
                ]);

                Chat::create([
                    'therapy_id' => $therapy->id,
                    'sender_id' => $doctor->user->id,
                    'receiver_id' => $patient->id,
                    'message' => match ($j) {
                        1 => 'Halo, terima kasih sudah menghubungi. Saya cek dulu jadwalnya, ya.',
                        2 => 'Sesi terapi kedua bisa dijadwalkan besok pada pukul 15.00 WIB, apakah waktu tersebut cocok untuk Anda?',
                    },
                ]);
            }

            // Add therapy schedules
            foreach ($therapyScheduleDescriptions as $week => $descriptions) {
                TherapySchedule::create([
                    'therapy_id' => $therapy->id,
                    'title' => 'Jadwal Sesi Terapi Minggu ke-'.($week + 1),
                    'description' => json_encode($descriptions),
                    'note' => 'Kamu sudah melakukan kemajuan dengan mulai mengenali pikiran negatif. Terus lanjutkan latihan yang sudah kita bahas.',
                    'date' => now()->addWeeks($week),
                    'is_completed' => true,
                    'link' => 'https://meet.google.com/msz-nzny-szk',
                    'time' => fake()->time('H:i'),
                    'created_at' => now(),
                ]);
            }

            // Notify once
            $admin = User::where('role', UserRole::ADMIN->value)->first();
            $admin->notify(new OrderedTherapy($therapy));
            $doctor->user->notify(new OrderedTherapy($therapy));
        }

        // Create 2 COMPLETED therapies
        for ($i = 0; $i < 2; $i++) {
            $patient = User::where('role', UserRole::PATIENT->value)->inRandomOrder()->first();
            $patient->is_therapy_in_progress = true;
            $patient->save();

            $therapy = Therapy::factory()->create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'status' => TherapyStatus::COMPLETED->value,
            ]);

            Order::factory()->create(['therapy_id' => $therapy->id]);

            $this->seedSleepDiaries($therapy);
            $this->seedIdentifyValue($therapy);
            $this->seedThoughtRecords($therapy);
            $this->seedEmotionRecords($therapy);
            $this->seedCommittedActions($therapy);

            for ($j = 1; $j < 3; $j++) {
                Chat::create([
                    'therapy_id' => $therapy->id,
                    'sender_id' => $patient->id,
                    'receiver_id' => $doctor->user->id,
                    'message' => match ($j) {
                        1 => 'Halo Dok, saya ingin menanyakan tentang jadwal sesi terapi kedua saya.',
                        2 => 'Apakah sudah ada kepastian tanggal dan jamnya?',
                    },
                ]);

                Chat::create([
                    'therapy_id' => $therapy->id,
                    'sender_id' => $doctor->user->id,
                    'receiver_id' => $patient->id,
                    'message' => match ($j) {
                        1 => 'Halo, terima kasih sudah menghubungi. Saya cek dulu jadwalnya, ya.',
                        2 => 'Sesi terapi kedua bisa dijadwalkan besok pada pukul 15.00 WIB, apakah waktu tersebut cocok untuk Anda?',
                    },
                ]);
            }

            // Add therapy schedules
            foreach ($therapyScheduleDescriptions as $week => $descriptions) {
                TherapySchedule::create([
                    'therapy_id' => $therapy->id,
                    'title' => 'Jadwal Sesi Terapi Minggu ke-'.($week + 1),
                    'description' => json_encode($descriptions),
                    'note' => 'Kamu sudah melakukan kemajuan dengan mulai mengenali pikiran negatif. Terus lanjutkan latihan yang sudah kita bahas.',
                    'date' => now()->addWeeks($week),
                    'is_completed' => true,
                    'link' => 'https://meet.google.com/msz-nzny-szk',
                    'time' => fake()->time('H:i'),
                    'created_at' => now(),
                ]);
            }

            $admin = User::where('role', UserRole::ADMIN->value)->first();
            $admin->notify(new OrderedTherapy($therapy));
            $doctor->user->notify(new OrderedTherapy($therapy));
        }
    }

    private function seedSleepDiaries(Therapy $therapy)
    {
        for ($week = 1; $week <= 6; $week++) {
            for ($day = 1; $day <= 7; $day++) {
                $currentDate = $therapy->start_date->addDays((($week - 1) * 7) + ($day - 1));
                $sleepDiaryTimestamp = $currentDate->setTime(rand(8, 22), rand(0, 59));

                $comment = $day == 1 ? 'Komentar dari psikolog untuk sleep diary minggu ke-'.$week : null;

                $sleepDiary = SleepDiary::create([
                    'therapy_id' => $therapy->id,
                    'week' => $week,
                    'day' => $day,
                    'date' => $currentDate->toDateString(),
                    'title' => 'Sleep Diary Minggu ke-'.$week,
                    'comment' => $comment,
                ]);

                $sleepDiaryQuestions = [
                    ['id' => 1, 'type' => QuestionType::BOOLEAN->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 2, 'type' => QuestionType::BOOLEAN->value, 'answer' => true, 'note' => 'Siang'],
                    ['id' => 3, 'type' => QuestionType::NUMBER->value, 'answer' => 2, 'note' => 'Siang'],
                    ['id' => 4, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Siang'],
                    ['id' => 5, 'type' => QuestionType::BOOLEAN->value, 'answer' => 'Penenang', 'note' => 'Siang'],
                    ['id' => 6, 'type' => QuestionType::NUMBER->value, 'answer' => 1, 'note' => 'Siang'],
                    ['id' => 7, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Siang'],
                    ['id' => 8, 'type' => QuestionType::BOOLEAN->value, 'answer' => fake()->boolean, 'note' => 'Siang'],
                    ['id' => 9, 'type' => QuestionType::BOOLEAN->value, 'answer' => fake()->boolean, 'note' => 'Siang'],
                    ['id' => 10, 'type' => QuestionType::BOOLEAN->value, 'answer' => fake()->boolean, 'note' => 'Siang'],
                    ['id' => 11, 'type' => QuestionType::BOOLEAN->value, 'answer' => fake()->boolean, 'note' => 'Siang'],
                    ['id' => 12, 'type' => QuestionType::BOOLEAN->value, 'answer' => fake()->boolean, 'note' => 'Siang'],
                    ['id' => 13, 'type' => QuestionType::BOOLEAN->value, 'answer' => fake()->boolean, 'note' => 'Siang'],
                    ['id' => 14, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Malam'],
                    ['id' => 15, 'type' => QuestionType::TIME->value, 'answer' => fake()->time('H:i'), 'note' => 'Malam'],
                    ['id' => 16, 'type' => QuestionType::NUMBER->value, 'answer' => fake()->numberBetween(0, 10), 'note' => 'Malam'],
                    ['id' => 17, 'type' => QuestionType::NUMBER->value, 'answer' => fake()->numberBetween(0, 5), 'note' => 'Malam'],
                    ['id' => 18, 'type' => QuestionType::NUMBER->value, 'answer' => fake()->numberBetween(4, 10), 'note' => 'Malam'],
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
            foreach ($questions as $index => $question) {
                $randomAnswer = match ($question['type']) {
                    QuestionType::NUMBER->value => match ($question['id']) {
                        20 => $answer20 = fake()->numberBetween(5, 10), // Save value for ID 20
                        22 => fake()->numberBetween(1, $answer20 ?? 10), // Use saved value for upper bound
                        default => fake()->numberBetween(1, 10),
                    },
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
                    'comment' => $index == 0 ? 'Komentar dari psikolog' : null,
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
            json_encode(['Saya selalu membuat gagal.', 'Saya selalu membuat kesalahan.']),
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
