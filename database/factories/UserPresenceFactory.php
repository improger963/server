<?php

namespace Database\Factories;

use App\Models\UserPresence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPresenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPresence::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'is_online' => $this->faker->boolean(70), // 70% chance of being online
            'last_seen' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ];
    }
    
    /**
     * Indicate that the user is online.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function online()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_online' => true,
            ];
        });
    }
    
    /**
     * Indicate that the user is offline.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function offline()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_online' => false,
            ];
        });
    }
}