<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instructor_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->unique()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => 'draft',
            'published_at' => null,
            'image' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => fake()->imageUrl(640, 360, 'education', true),
        ]);
    }
}