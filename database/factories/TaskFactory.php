<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::first(); // Ensure this is the first user in the database

        return [
            'user_id' => $user, // Associate with first user or create a new one if none exists
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'in-progress', 'completed']),
        ];
    }
}
