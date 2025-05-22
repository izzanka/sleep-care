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
        return [
            'status' => OrderStatus::SUCCESS->value,
            'payment_status' => OrderStatus::SETTLEMENT->value,
            'payment_type' => 'bank_transfer',
            'total_price' => 370000,
            'created_at' => now(),
        ];
    }
}
