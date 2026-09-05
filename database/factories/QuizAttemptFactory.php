<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    protected $model = QuizAttempt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'quiz_id' => Quiz::factory(),
            'score' => fake()->numberBetween(0, 100),
            'passed' => fake()->boolean(50),
            'attempted_at' => now(),
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(60, 100),
            'passed' => true,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(0, 59),
            'passed' => false,
        ]);
    }
}