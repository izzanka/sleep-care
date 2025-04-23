<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
        $start_date = now();
        $end_date = $start_date->addWeeks(6);

        return [
            'start_date' => $start_date->toDateString(),
            'end_date' => $end_date->toDateString(),
            'doctor_fee' => 350000,
            'application_fee' => 20000,
            'created_at' => now(),
        ];
    }
}
