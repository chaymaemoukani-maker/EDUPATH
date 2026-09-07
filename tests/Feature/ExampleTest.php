<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The homepage renders the EduPath value proposition and public sections.
     */
    public function test_the_homepage_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('EduPath');
        $response->assertSee('Explorer le catalogue');
        $response->assertSee('Créer un compte gratuit');
        $response->assertSee('Comment ça marche');
    }

    /**
     * Published courses and categories are featured on the homepage (real data only).
     */
    public function test_the_homepage_features_published_courses_and_categories(): void
    {
        $category = Category::factory()->create(['name' => 'Développement']);
        $course = Course::factory()->published()->create(['category_id' => $category->id]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee($course->title);
        $response->assertSee('Développement');
    }

    /**
     * Draft courses never appear on the public homepage.
     */
    public function test_the_homepage_never_shows_draft_courses(): void
    {
        $draft = Course::factory()->create();

        $this->get('/')
            ->assertStatus(200)
            ->assertDontSee($draft->title);
    }
}