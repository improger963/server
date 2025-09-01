<?php

namespace Database\Factories;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatMessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ChatMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'recipient_id' => User::factory(),
            'message' => $this->faker->sentence(),
            'is_read' => $this->faker->boolean(30), // 30% chance of being read
        ];
    }
}