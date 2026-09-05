<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => 'Quiz : '.fake()->words(3, true),
            'pass_score' => fake()->numberBetween(50, 80),
            'max_attempts' => 3,
        ];
    }
}