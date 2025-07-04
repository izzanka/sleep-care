<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Therapy>
 */
class TherapyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'start_date' => now()->subDays(42)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'doctor_fee' => 480000,
            'application_fee' => 20000,
            'created_at' => now(),
        ];
    }
}
