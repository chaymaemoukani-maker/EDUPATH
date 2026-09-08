<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->instructor = User::factory()->create();
    $this->instructor->addRole('instructor');

    $this->category = Category::factory()->create();
});

test('learner cannot access the instructor space', function () {
    $learner = User::factory()->create();
    $learner->addRole('learner');

    $this->actingAs($learner)
        ->get(route('instructor.courses.index'))
        ->assertForbidden();

    $this->actingAs($learner)
        ->get(route('instructor.learners.index'))
        ->assertForbidden();
});

test('instructor can create a course in draft status', function () {
    $this->actingAs($this->instructor)
        ->post(route('instructor.courses.store'), [
            'title' => 'Mon premier cours',
            'description' => 'Description du cours',
            'category_id' => $this->category->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $course = Course::where('title', 'Mon premier cours')->first();
    expect($course)->not->toBeNull()
        ->and($course->status)->toBe('draft')
        ->and($course->instructor_id)->toBe($this->instructor->id);
});

test('instructor cannot edit another instructor course', function () {
    $other = User::factory()->create();
    $other->addRole('instructor');
    $course = Course::factory()->create(['instructor_id' => $other->id]);

    $this->actingAs($this->instructor)
        ->get(route('instructor.courses.edit', $course))
        ->assertForbidden();
});

test('instructor cannot publish their own course (admin-only)', function () {
    $course = Course::factory()->create(['instructor_id' => $this->instructor->id]);

    expect($this->instructor->can('publish', $course))->toBeFalse();

    $this->actingAs($this->instructor)
        ->patch(route('admin.courses.publish', $course))
        ->assertForbidden();
});

test('instructor can add a section with incremental ordering', function () {
    $course = Course::factory()->create(['instructor_id' => $this->instructor->id]);

    $this->actingAs($this->instructor)
        ->post(route('instructor.sections.store'), [
            'course_id' => $course->id,
            'title' => 'Introduction',
        ])
        ->assertRedirect();

    $section = $course->refresh()->sections()->first();
    expect($section)->not->toBeNull()
        ->and($section->order)->toBe(1);
});

test('instructor cannot add a section to another course', function () {
    $other = User::factory()->create();
    $other->addRole('instructor');
    $course = Course::factory()->create(['instructor_id' => $other->id]);

    $this->actingAs($this->instructor)
        ->post(route('instructor.sections.store'), [
            'course_id' => $course->id,
            'title' => 'Intrusion',
        ])
        ->assertForbidden();

    expect($course->sections()->count())->toBe(0);
});

test('instructor can add a text module', function () {
    $course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
    $section = Section::factory()->create(['course_id' => $course->id]);

    $this->actingAs($this->instructor)
        ->post(route('instructor.modules.store'), [
            'section_id' => $section->id,
            'title' => 'Module de texte',
            'type' => 'text',
            'content' => 'Contenu du module',
        ])
        ->assertRedirect();

    $module = Module::where('title', 'Module de texte')->first();
    expect($module)->not->toBeNull()
        ->and($module->content)->toBe('Contenu du module');
});

test('instructor can add a video module', function () {
    $course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
    $section = Section::factory()->create(['course_id' => $course->id]);

    $this->actingAs($this->instructor)
        ->post(route('instructor.modules.store'), [
            'section_id' => $section->id,
            'title' => 'Vidéo de démo',
            'type' => 'video',
            'content' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])
        ->assertRedirect();

    expect(Module::where('title', 'Vidéo de démo')->exists())->toBeTrue();
});

test('instructor can add a pdf module with a file upload', function () {
    Storage::fake('public');

    $course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
    $section = Section::factory()->create(['course_id' => $course->id]);

    $this->actingAs($this->instructor)
        ->post(route('instructor.modules.store'), [
            'section_id' => $section->id,
            'title' => 'Support PDF',
            'type' => 'pdf',
            'content' => UploadedFile::fake()->create('support.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect();

    $module = Module::where('title', 'Support PDF')->first();
    expect($module)->not->toBeNull()
        ->and(Storage::disk('public')->exists($module->content))->toBeTrue();
});

test('instructor cannot add a module to another course section', function () {
    $other = User::factory()->create();
    $other->addRole('instructor');
    $course = Course::factory()->create(['instructor_id' => $other->id]);
    $section = Section::factory()->create(['course_id' => $course->id]);

    $this->actingAs($this->instructor)
        ->post(route('instructor.modules.store'), [
            'section_id' => $section->id,
            'title' => 'Intrusion',
            'type' => 'text',
            'content' => 'Contenu',
        ])
        ->assertForbidden();

    expect($section->modules()->count())->toBe(0);
});

test('instructor can create a quiz for their own module from JSON payload', function () {
    $course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
    $section = Section::factory()->create(['course_id' => $course->id]);
    $module = Module::factory()->create(['section_id' => $section->id, 'type' => 'text']);

    $this->actingAs($this->instructor)
        ->post(route('instructor.quizzes.store'), [
            'module_id' => $module->id,
            'title' => 'Quiz de validation',
            'pass_score' => 50,
            'max_attempts' => 3,
            'questions_json' => json_encode([
                ['text' => 'Quelle réponse est correcte ?', 'answers' => ['A', 'B'], 'correct_answer' => 1],
            ]),
        ])
        ->assertRedirect();

    $quiz = Quiz::where('module_id', $module->id)->first();
    $question = $quiz?->questions()->first();
    expect($quiz)->not->toBeNull()
        ->and($quiz->title)->toBe('Quiz de validation')
        ->and($quiz->questions()->count())->toBe(1)
        ->and($question->answers()->count())->toBe(2)
        ->and($question->answers()->where('is_correct', true)->first()->text)->toBe('B');
});

test('only one quiz per module', function () {
    $course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
    $section = Section::factory()->create(['course_id' => $course->id]);
    $module = Module::factory()->create(['section_id' => $section->id, 'type' => 'text']);
    Quiz::factory()->create(['module_id' => $module->id]);

    $this->actingAs($this->instructor)
        ->post(route('instructor.quizzes.store'), [
            'module_id' => $module->id,
            'title' => 'Duplicata',
            'pass_score' => 70,
            'max_attempts' => 3,
            'questions_json' => json_encode([
                ['text' => 'Question ?', 'answers' => ['A', 'B'], 'correct_answer' => 0],
            ]),
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($module->quiz)->not->toBeNull();
});

test('quiz validation rejects an out-of-range correct answer index', function () {
    $course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
    $section = Section::factory()->create(['course_id' => $course->id]);
    $module = Module::factory()->create(['section_id' => $section->id, 'type' => 'text']);

    $this->actingAs($this->instructor)
        ->post(route('instructor.quizzes.store'), [
            'module_id' => $module->id,
            'title' => 'Quiz invalide',
            'pass_score' => 50,
            'max_attempts' => 3,
            'questions_json' => json_encode([
                ['text' => 'Question ?', 'answers' => ['A', 'B'], 'correct_answer' => 5],
            ]),
        ])
        ->assertSessionHasErrors('questions.0.correct_answer');

    expect(Quiz::where('module_id', $module->id)->exists())->toBeFalse();
});

test('instructor can view the learners enrolled in their own courses', function () {
    $course = Course::factory()->create(['instructor_id' => $this->instructor->id]);

    $learner = User::factory()->create();
    $learner->addRole('learner');
    Enrollment::factory()->create(['user_id' => $learner->id, 'course_id' => $course->id]);

    $this->actingAs($this->instructor)
        ->get(route('instructor.learners.index'))
        ->assertOk()
        ->assertSee('Apprenants inscrits')
        ->assertSee($learner->name)
        ->assertSee($learner->email)
        ->assertSee($course->title);
});

test('instructor cannot see learners enrolled in another instructor courses', function () {
    $other = User::factory()->create();
    $other->addRole('instructor');
    $otherCourse = Course::factory()->create(['instructor_id' => $other->id]);

    $learner = User::factory()->create();
    $learner->addRole('learner');
    Enrollment::factory()->create(['user_id' => $learner->id, 'course_id' => $otherCourse->id]);

    $this->actingAs($this->instructor)
        ->get(route('instructor.learners.index'))
        ->assertOk()
        ->assertDontSee($learner->name)
        ->assertDontSee($learner->email);
});

test('instructor cannot filter the learners page by a course they do not own', function () {
    $other = User::factory()->create();
    $other->addRole('instructor');
    $otherCourse = Course::factory()->create(['instructor_id' => $other->id]);

    $this->actingAs($this->instructor)
        ->get(route('instructor.learners.index', ['course' => $otherCourse->id]))
        ->assertForbidden();
});