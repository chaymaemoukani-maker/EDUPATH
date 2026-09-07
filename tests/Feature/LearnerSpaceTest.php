<?php

use App\Models\Answer;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->learner = User::factory()->create();
    $this->learner->addRole('learner');

    $this->otherLearner = User::factory()->create();
    $this->otherLearner->addRole('learner');
});

function makePublishedCourseWithModules(int $moduleCount = 2): array
{
    $course = Course::factory()->published()->create();
    $section = Section::factory()->create(['course_id' => $course->id, 'order' => 1]);
    $modules = [];

    for ($i = 1; $i <= $moduleCount; $i++) {
        $modules[] = Module::factory()->create([
            'section_id' => $section->id,
            'type' => 'text',
            'order' => $i,
        ]);
    }

    return [$course, $section, $modules];
}

test('catalog only shows published courses', function () {
    Course::factory()->published()->create(['title' => 'Cours public']);
    Course::factory()->create(['title' => 'Cours brouillon non publié']);

    $this->get(route('catalog'))
        ->assertOk()
        ->assertSee('Cours public')
        ->assertDontSee('Cours brouillon non publié');
});

test('catalog filters by category and search query', function () {
    $course = Course::factory()->published()->create(['title' => 'Laravel avancé']);

    $this->get(route('catalog', ['search' => 'Laravel']))
        ->assertOk()
        ->assertSee('Laravel avancé');

    $this->get(route('catalog', ['category' => $course->category_id]))
        ->assertOk()
        ->assertSee('Laravel avancé');

    $this->get(route('catalog', ['category' => 999999]))
        ->assertSessionHasErrors('category');
});

test('course detail is public but hides the content before enrollment', function () {
    [$course, $section, $modules] = makePublishedCourseWithModules(1);
    $content = $modules[0]->content;

    $this->get(route('catalog.show', $course))
        ->assertOk()
        ->assertSee($course->title)
        ->assertSee('inscrire gratuitement')
        ->assertDontSee($content);
});

test('unpublished course detail is forbidden for guests', function () {
    $draft = Course::factory()->create();

    $this->get(route('catalog.show', $draft))->assertForbidden();
});

test('guest cannot reach the learner space', function () {
    $this->get(route('learner.courses.index'))->assertRedirect(route('login'));
});

test('instructor cannot reach the learner space', function () {
    $instructor = User::factory()->create();
    $instructor->addRole('instructor');

    $this->actingAs($instructor)
        ->get(route('learner.courses.index'))
        ->assertForbidden();
});

test('learner can enroll into a published course', function () {
    [$course] = makePublishedCourseWithModules();

    $this->actingAs($this->learner)
        ->post(route('learner.enrollments.store', $course))
        ->assertRedirect(route('learner.courses.show', $course));

    expect(Enrollment::where('user_id', $this->learner->id)->where('course_id', $course->id)->exists())->toBeTrue();
});

test('learner cannot enroll into an unpublished course', function () {
    $draft = Course::factory()->create();

    $this->actingAs($this->learner)
        ->post(route('learner.enrollments.store', $draft))
        ->assertForbidden();
});

test('learner cannot enroll twice into the same course', function () {
    [$course] = makePublishedCourseWithModules(1);
    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $this->actingAs($this->learner)
        ->post(route('learner.enrollments.store', $course))
        ->assertForbidden();

    expect(Enrollment::where('user_id', $this->learner->id)->where('course_id', $course->id)->count())->toBe(1);
});

test('module content is only accessible to enrolled learners', function () {
    [$course, $section, $modules] = makePublishedCourseWithModules(1);

    $this->actingAs($this->otherLearner)
        ->get(route('learner.modules.show', $modules[0]))
        ->assertForbidden();

    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $this->actingAs($this->learner)
        ->get(route('learner.modules.show', $modules[0]))
        ->assertOk()
        ->assertSee($modules[0]->content);
});

test('marking a module complete creates the progress line and updates the percent', function () {
    [$course] = makePublishedCourseWithModules(2);
    [$m1, $m2] = $course->sections->first()->modules;
    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $this->actingAs($this->learner)
        ->post(route('learner.progress.store', $m1))
        ->assertRedirect(route('learner.modules.show', $m1));

    expect($m1->progress()->where('user_id', $this->learner->id)->count())->toBe(1)
        ->and($this->learner->progress()->count())->toBe(1);

    $this->actingAs($this->learner)
        ->get(route('learner.courses.show', $course))
        ->assertOk()
        ->assertSee('50%');
});

test('completing the same module twice only creates one progress line', function () {
    [$course, $section, $modules] = makePublishedCourseWithModules(1);
    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $this->actingAs($this->learner)->post(route('learner.progress.store', $modules[0]));
    $this->actingAs($this->learner)->post(route('learner.progress.store', $modules[0]));

    expect($modules[0]->progress()->where('user_id', $this->learner->id)->count())->toBe(1);
});

test('reaching 100% progress generates a certificate and completes the enrollment', function () {
    [$course, $section, $modules] = makePublishedCourseWithModules(2);
    $enrollment = Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    foreach ($modules as $module) {
        $this->actingAs($this->learner)->post(route('learner.progress.store', $module));
    }

    $certificate = Certificate::where('user_id', $this->learner->id)->where('course_id', $course->id)->first();

    expect($certificate)->not->toBeNull()
        ->and($certificate->unique_code)->not->toBeNull()
        ->and($this->learner->certificates()->where('course_id', $course->id)->count())->toBe(1)
        ->and($enrollment->refresh()->completed_at)->not->toBeNull();

    $this->actingAs($this->learner)
        ->get(route('learner.courses.show', $course))
        ->assertOk()
        ->assertSee('100%');
});

test('quiz page requires an enrolled learner', function () {
    [$course, $section, $modules] = makePublishedCourseWithModules(1);
    $quiz = Quiz::factory()->create(['module_id' => $modules[0]->id, 'pass_score' => 50, 'max_attempts' => 3]);
    $question = Question::factory()->create(['quiz_id' => $quiz->id]);
    $correct = Answer::factory()->correct()->create(['question_id' => $question->id]);

    $this->actingAs($this->otherLearner)
        ->get(route('learner.quizzes.show', $quiz))
        ->assertForbidden();

    $this->actingAs($this->otherLearner)
        ->post(route('learner.quizzes.store', $quiz), ['answers' => [$question->id => $correct->id]])
        ->assertForbidden();
});

test('passing a quiz records the attempt and completes the module', function () {
    [$course, $section, $modules] = makePublishedCourseWithModules(1);
    $module = $modules[0];
    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $quiz = Quiz::factory()->create(['module_id' => $module->id, 'pass_score' => 50, 'max_attempts' => 3]);
    $question = Question::factory()->create(['quiz_id' => $quiz->id]);
    $correct = Answer::factory()->correct()->create(['question_id' => $question->id]);
    Answer::factory()->create(['question_id' => $question->id]);

    $this->actingAs($this->learner)
        ->post(route('learner.quizzes.store', $quiz), [
            'answers' => [$question->id => $correct->id],
        ])
        ->assertRedirect(route('learner.quizzes.show', $quiz));

    $attempt = $this->learner->quizAttempts()->where('quiz_id', $quiz->id)->first();

    expect($attempt)->not->toBeNull()
        ->and($attempt->score)->toBe(100)
        ->and($attempt->passed)->toBeTrue()
        ->and($module->progress()->where('user_id', $this->learner->id)->exists())->toBeTrue();
});

test('failing a quiz does not complete the module', function () {
    [$course, $section, $modules] = makePublishedCourseWithModules(1);
    $module = $modules[0];
    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $quiz = Quiz::factory()->create(['module_id' => $module->id, 'pass_score' => 50, 'max_attempts' => 3]);
    $question = Question::factory()->create(['quiz_id' => $quiz->id]);
    Answer::factory()->correct()->create(['question_id' => $question->id]);
    $wrong = Answer::factory()->create(['question_id' => $question->id]);

    $this->actingAs($this->learner)
        ->post(route('learner.quizzes.store', $quiz), [
            'answers' => [$question->id => $wrong->id],
        ]);

    $attempt = $this->learner->quizAttempts()->where('quiz_id', $quiz->id)->first();

    expect($attempt->passed)->toBeFalse()
        ->and($module->progress()->where('user_id', $this->learner->id)->exists())->toBeFalse();
});

test('no attempt is possible once max_attempts is exhausted', function () {
    [$course, $section, $modules] = makePublishedCourseWithModules(1);
    $module = $modules[0];
    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $quiz = Quiz::factory()->create(['module_id' => $module->id, 'pass_score' => 50, 'max_attempts' => 1]);
    $question = Question::factory()->create(['quiz_id' => $quiz->id]);
    Answer::factory()->correct()->create(['question_id' => $question->id]);
    $wrong = Answer::factory()->create(['question_id' => $question->id]);

    $this->actingAs($this->learner)
        ->post(route('learner.quizzes.store', $quiz), ['answers' => [$question->id => $wrong->id]])
        ->assertRedirect();

    $this->actingAs($this->learner)
        ->post(route('learner.quizzes.store', $quiz), ['answers' => [$question->id => $wrong->id]])
        ->assertForbidden();

    $this->actingAs($this->learner)
        ->get(route('learner.quizzes.show', $quiz))
        ->assertOk()
        ->assertSee('Tentatives épuisées');
});

test('a passed quiz blocks any further attempt', function () {
    [$course, $section, $modules] = makePublishedCourseWithModules(1);
    $module = $modules[0];
    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $quiz = Quiz::factory()->create(['module_id' => $module->id, 'pass_score' => 50, 'max_attempts' => 3]);
    $question = Question::factory()->create(['quiz_id' => $quiz->id]);
    $correct = Answer::factory()->correct()->create(['question_id' => $question->id]);

    $this->actingAs($this->learner)
        ->post(route('learner.quizzes.store', $quiz), ['answers' => [$question->id => $correct->id]]);

    $this->actingAs($this->learner)
        ->post(route('learner.quizzes.store', $quiz), ['answers' => [$question->id => $correct->id]])
        ->assertForbidden();
});

test('certificate list only shows the learner own certificates', function () {
    $courseA = Course::factory()->published()->create();
    $courseB = Course::factory()->published()->create();

    $mine = Certificate::factory()->create(['user_id' => $this->learner->id, 'course_id' => $courseA->id]);
    $others = Certificate::factory()->create(['user_id' => $this->otherLearner->id, 'course_id' => $courseB->id]);

    $this->actingAs($this->learner)
        ->get(route('learner.certificates.index'))
        ->assertOk()
        ->assertSee($mine->unique_code)
        ->assertDontSee($others->unique_code);
});

test('a learner can only download their own certificate', function () {
    [$course] = makePublishedCourseWithModules(1);
    $certificate = Certificate::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $this->actingAs($this->otherLearner)
        ->get(route('learner.certificates.download', $certificate))
        ->assertForbidden();

    $response = $this->actingAs($this->learner)
        ->get(route('learner.certificates.download', $certificate));

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('content-type'))->toContain('application/pdf');
});

test('public verification finds a valid certificate and rejects an unknown code', function () {
    [$course] = makePublishedCourseWithModules(1);
    $certificate = Certificate::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $this->get(route('verify', ['code' => $certificate->unique_code]))
        ->assertOk()
        ->assertSee($certificate->user->name)
        ->assertSee($certificate->course->title);

    $this->get(route('verify', ['code' => 'CODE-INCONNU']))
        ->assertOk()
        ->assertSee('Aucun certificat');
});

test('learner dashboard shows the enrolled courses and progression', function () {
    [$course] = makePublishedCourseWithModules(1);
    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $course->id]);

    $this->actingAs($this->learner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Cours en cours')
        ->assertSee($course->title);

    $this->actingAs($this->otherLearner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Cours inscrits');
});