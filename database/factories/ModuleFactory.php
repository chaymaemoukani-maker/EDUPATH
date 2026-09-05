<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    protected $model = Module::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(['text', 'video', 'pdf']),
            'content' => fake()->paragraphs(2, true),
            'order' => fake()->numberBetween(1, 10),
        ];
    }

    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'text',
            'content' => fake()->paragraphs(3, true),
        ]);
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'content' => fake()->url(),
        ]);
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pdf',
            'content' => fake()->url(),
        ]);
    }
}