<?php

use App\Http\Requests\StoreModuleRequest;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------

test('login is rate limited after five failed attempts', function () {
    foreach (range(1, 5) as $i) {
        $this->post(route('login'), [
            'email' => 'rate-limited@example.com',
            'password' => 'wrong-password',
        ]);
    }

    $this->post(route('login'), [
        'email' => 'rate-limited@example.com',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors();

    $this->assertGuest();

    $key = Str::transliterate(Str::lower('rate-limited@example.com').'|127.0.0.1');
    expect(RateLimiter::tooManyAttempts($key, 5))->toBeTrue();
});

test('registration is rate limited', function () {
    foreach (range(1, 10) as $i) {
        $this->post(route('register'), [
            'name' => "Utilisateur $i",
            'email' => "nouveau-$i@example.com",
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));
    }

    $this->post(route('register'), [
        'name' => 'Utilisateur 11',
        'email' => 'nouveau-11@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertStatus(429);
});

test('public certificate verification is rate limited', function () {
    foreach (range(1, 15) as $i) {
        $this->get(route('verify', ['code' => "CODE-$i"]));
    }

    $this->get(route('verify', ['code' => 'TROP-DEMANDE']))->assertStatus(429);
});

// ---------------------------------------------------------------------------
// Custom error pages
// ---------------------------------------------------------------------------

test('custom 404 page is rendered', function () {
    $this->get('/url-inexistante')
        ->assertStatus(404)
        ->assertSee('Page introuvable')
        ->assertSee("Retour à l'accueil", false);
});

test('custom 403 page is rendered for unauthorized role access', function () {
    $learner = User::factory()->create();
    $learner->addRole('learner');

    $this->actingAs($learner)
        ->get(route('admin.dashboard'))
        ->assertForbidden()
        ->assertSee('Accès refusé');
});

// ---------------------------------------------------------------------------
// Module validation: type whitelist + upload security
// ---------------------------------------------------------------------------

function instructorWithCourse(): array
{
    $instructor = User::factory()->create();
    $instructor->addRole('instructor');
    $course = Course::factory()->create(['instructor_id' => $instructor->id]);
    $section = Section::factory()->create(['course_id' => $course->id, 'order' => 1]);

    return [$instructor, $course, $section];
}

test('module type "quiz" is rejected (quiz is a separate entity)', function () {
    [$instructor] = instructorWithCourse();

    $this->actingAs($instructor)
        ->post(route('instructor.modules.store'), [
            'section_id' => Section::first()->id,
            'title' => 'Module avec mauvais type',
            'type' => 'quiz',
            'content' => 'du contenu',
        ])
        ->assertSessionHasErrors('type');
});

test('non-file content is rejected for pdf modules', function () {
    [$instructor, $course, $section] = instructorWithCourse();

    $this->actingAs($instructor)
        ->post(route('instructor.modules.store'), [
            'section_id' => $section->id,
            'title' => 'Fichier non autorisé',
            'type' => 'pdf',
            'content' => 'contenu texte non fichier',
        ])
        ->assertSessionHasErrors('content');

    expect(Module::count())->toBe(0);
});

test('pdf module uploads are restricted to pdf files under 10 Mo', function () {
    $request = new StoreModuleRequest();
    $request->merge(['type' => 'pdf']);
    $rules = Arr::flatten($request->rules());

    expect($rules)->toContain('file')
        ->and($rules)->toContain('mimes:pdf')
        ->and($rules)->toContain('max:10240');
});

// ---------------------------------------------------------------------------
// Demo data seeder
// ---------------------------------------------------------------------------

test('demo seeder creates a coherent dataset for scenarios A to F', function () {
    $this->seed(DemoDataSeeder::class);

    expect(Category::count())->toBeGreaterThanOrEqual(4);

    expect(Course::where('status', 'draft')->count())->toBeGreaterThanOrEqual(1) // A
        ->and(Course::where('status', 'published')->count())->toBeGreaterThanOrEqual(3); // B

    expect(Enrollment::count())->toBeGreaterThanOrEqual(3) // C
        ->and(Progress::count())->toBeGreaterThanOrEqual(2); // D

    expect(Certificate::count())->toBe(1) // E
        ->and(Certificate::first()->unique_code)->toMatch('/^[A-Z0-9]{12}$/');

    expect(Quiz::whereHas('questions', fn ($query) => $query->has('answers', '>=', 4))->count())->toBeGreaterThanOrEqual(1) // F
        ->and(QuizAttempt::where('passed', true)->count())->toBeGreaterThanOrEqual(1);

    expect(User::whereHas('roles', fn ($query) => $query->where('name', 'instructor'))->count())->toBeGreaterThanOrEqual(3)
        ->and(User::whereHas('roles', fn ($query) => $query->where('name', 'learner'))->count())->toBeGreaterThanOrEqual(3);

    // Constraint checks: no "quiz" module type, no score-related field on certificates.
    expect(Module::where('type', 'quiz')->count())->toBe(0)
        ->and(Certificate::first()->getAttributes())->not->toHaveKey('score');
});