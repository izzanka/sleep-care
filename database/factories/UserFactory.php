<?php

namespace Database\Factories;

use App\Enum\Problem;
use App\Enum\UserGender;
use App\Enum\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(UserGender::cases())->value;

        return [
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'age' => fake()->numberBetween(20, 60),
            'gender' => $gender,
            'created_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin()
    {
        return $this->state([
            'name' => 'admin',
            'email' => 'admin@sleepcares.xyz',
            'role' => UserRole::ADMIN->value,
            'email_verified_at' => now(),
            'balance' => 40000,
        ]);
    }

    public function doctor()
    {
        return $this->state([
            'name' => fake()->name().' M.Psi',
            'role' => UserRole::DOCTOR->value,
        ]);
    }

    public function patient()
    {
        $problems = collect(Problem::cases())
            ->random(2)
            ->map(fn ($problem) => $problem->value)
            ->toArray();

        $jsonProblems = json_encode($problems);

        return $this->state([
            'name' => fake()->name(),
            'role' => UserRole::PATIENT->value,
            'problems' => $jsonProblems,
            'email_verified_at' => now(),
        ]);
    }
}
