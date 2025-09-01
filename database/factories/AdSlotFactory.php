<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Site;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdSlot>
 */
class AdSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => fake()->word() . ' Ad Slot',
            'size' => fake()->randomElement(['300x250', '728x90', '160x600', '300x600', '970x250']),
            'price_per_click' => fake()->randomFloat(4, 0.01, 2.00),
            'price_per_impression' => fake()->randomFloat(4, 0.001, 0.10),
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }
}
