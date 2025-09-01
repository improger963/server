<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'processed']),
            'processed_at' => $this->faker->optional()->dateTime(),
            'transaction_id' => $this->faker->optional()->uuid(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}