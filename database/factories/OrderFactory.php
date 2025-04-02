<?php

namespace Database\Factories;

use App\Enum\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(OrderStatus::cases())->value;

        return [
            'status' => $status,
            'payment_status' => $status,
            'payment_method' => 'Bank Transfer',
            'payment_token' => 'payment_token',
            'total_price' => 370000,
            'created_at' => now(),
        ];
    }
}
