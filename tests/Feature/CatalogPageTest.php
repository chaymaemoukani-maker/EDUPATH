<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A published course with a cover renders its image on the catalog.
     */
    public function test_the_catalog_renders_course_images(): void
    {
        $course = Course::factory()->published()->create([
            'image' => '/images/courses/laravel.svg',
            'title' => 'Cours avec visuel',
        ]);

        $this->get(route('catalog'))
            ->assertStatus(200)
            ->assertSee('src="/images/courses/laravel.svg"', false)
            ->assertSee($course->title);
    }

    /**
     * A published course without an image still renders, using the placeholder fallback.
     */
    public function test_the_catalog_keeps_the_fallback_for_courses_without_image(): void
    {
        $course = Course::factory()->published()->create([
            'image' => null,
            'title' => 'Cours sans visuel',
        ]);

        $this->get(route('catalog'))
            ->assertStatus(200)
            ->assertSee($course->title)
            ->assertSee('M12 6.042A8.967', false)
            ->assertDontSee('src="/images/courses', false);
    }

    /**
     * The fallback is used on the homepage too (featured courses).
     */
    public function test_the_homepage_renders_the_course_cover_or_fallback(): void
    {
        $category = Category::factory()->create(['name' => 'Marketing']);
        Course::factory()->published()->create([
            'category_id' => $category->id,
            'image' => '/images/courses/marketing.svg',
            'title' => 'Cours marketing',
        ]);
        Course::factory()->published()->create(['image' => null, 'title' => 'Cours sans visuel']);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('src="/images/courses/marketing.svg"', false)
            ->assertSee('M12 6.042A8.967', false);
    }
}