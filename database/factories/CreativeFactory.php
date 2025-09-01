<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Campaign;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Creative>
 */
class CreativeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'name' => fake()->sentence(2),
            'type' => fake()->randomElement(['banner', 'text']),
            'content' => fake()->randomElement([
                '<img src="https://example.com/banner.jpg" alt="Ad Banner" />',
                fake()->sentence(5)
            ]),
            'url' => fake()->url(),
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }
}
