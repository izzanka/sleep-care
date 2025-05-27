<?php

namespace Database\Seeders;

use App\Enum\TherapyStatus;
use App\Enum\UserRole;
use App\Models\Chat;
use App\Models\Doctor;
use App\Models\Order;
use App\Models\Therapy;
use App\Models\User;
use Illuminate\Database\Seeder;

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

        $patient = User::select('id', 'role')->where('role', UserRole::PATIENT->value)->first();
        $patient->is_therapy_in_progress = true;
        $patient->save();

        $therapyInProgress = Therapy::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => TherapyStatus::IN_PROGRESS->value,
        ]);

        Order::factory()->create(['therapy_id' => $therapyInProgress->id]);

        for ($i = 1; $i < 7; $i++) {
            Chat::create([
                'therapy_id' => $therapyInProgress->id,
                'sender_id' => $patient->id,
                'receiver_id' => $doctor->user->id,
                'message' => fake()->sentence,
            ]);

            Chat::create([
                'therapy_id' => $therapyInProgress->id,
                'sender_id' => $doctor->user->id,
                'receiver_id' => $patient->id,
                'message' => fake()->sentence,
            ]);
        }

        $therapyCompleted = Therapy::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => TherapyStatus::COMPLETED->value,
        ]);

        Order::factory()->create(['therapy_id' => $therapyCompleted->id]);

        for ($j = 1; $j < 7; $j++) {
            Chat::create([
                'therapy_id' => $therapyCompleted->id,
                'sender_id' => $patient->id,
                'receiver_id' => $doctor->user->id,
                'message' => fake()->sentence,
            ]);

            Chat::create([
                'therapy_id' => $therapyCompleted->id,
                'sender_id' => $doctor->user->id,
                'receiver_id' => $patient->id,
                'message' => fake()->sentence,
            ]);
        }

    }
}
