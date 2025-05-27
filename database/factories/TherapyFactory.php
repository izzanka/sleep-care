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
            'start_date' => now()->subWeeks(6)->toDateString(),
            'end_date' => now()->toDateString(),
            'doctor_fee' => 350000,
            'application_fee' => 20000,
            'created_at' => now(),
        ];
    }
}
