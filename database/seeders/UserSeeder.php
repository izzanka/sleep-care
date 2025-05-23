<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\General;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        General::create([
            'doctor_fee' => 350000,
            'application_fee' => 20000,
        ]);

        User::factory()->patient()->count(30)->create();

        User::factory()->doctor()->count(30)->create()->each(function ($user) {
            Doctor::factory()->create([
                'user_id' => $user->id,
            ]);
        });

        User::factory()->admin()->create();
        $userDoctor = User::factory()->doctor()->create([
            'name' => 'psikolog',
            'email' => 'info@sleepcares.xyz',
            'balance' => 350000,
        ]);

        Doctor::factory()->create([
            'user_id' => $userDoctor->id,
        ]);
    }
}
