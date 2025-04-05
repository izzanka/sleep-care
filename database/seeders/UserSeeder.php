<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
        $userDoctor = User::factory()->doctor()->create([
            'name' => 'psikolog',
            'email' => 'psikolog@sleepcare.com',
        ]);

        Doctor::factory()->create([
            'user_id' => $userDoctor->id,
            'name_title' => fake()->randomElement(['Dr.', 'Prof.', 'Mr.', 'Ms.']).' '.$userDoctor->name,
        ]);

        $admin->deposit(20000);
        $userDoctor->deposit(350000);
    }
}
