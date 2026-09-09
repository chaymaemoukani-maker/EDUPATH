<?php

use App\Events\CourseCompleted;
use App\Jobs\GenerateCertificateJob;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

function completedEnrollment(User $user, Course $course): Enrollment
{
    return Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
    ]);
}

function makeCourseWithModules(int $count): array
{
    $course = Course::factory()->published()->create();
    $section = Section::factory()->create(['course_id' => $course->id, 'order' => 1]);
    $modules = [];

    for ($i = 1; $i <= $count; $i++) {
        $modules[] = Module::factory()->create([
            'section_id' => $section->id,
            'type' => 'text',
            'order' => $i,
        ]);
    }

    return [$course, $section, $modules];
}

test('the CourseCompleted event can be dispatched and exposes the user and course', function () {
    $user = User::factory()->create();
    [$course] = makeCourseWithModules(1);

    $fired = false;
    Event::listen(CourseCompleted::class, function (CourseCompleted $event) use (&$fired, $user, $course) {
        $fired = true;
        expect($event->user->id)->toBe($user->id)
            ->and($event->course->id)->toBe($course->id);
    });

    CourseCompleted::dispatch($user, $course);

    expect($fired)->toBeTrue();
});

test('CourseCompleted triggers the GenerateCertificate listener which dispatches the queued job', function () {
    Queue::fake();
    $user = User::factory()->create();
    [$course] = makeCourseWithModules(1);

    $this->app['events']->dispatch(new CourseCompleted($user, $course));

    Queue::assertPushed(GenerateCertificateJob::class, fn (GenerateCertificateJob $job) => $job->user->id === $user->id && $job->course->id === $course->id);
});

test('the Job creates the certificate and completes the enrollment when executed', function () {
    Queue::fake();
    $user = User::factory()->create();
    [$course] = makeCourseWithModules(1);
    $enrollment = completedEnrollment($user, $course);

    (new GenerateCertificateJob($user, $course))->handle();

    $certificate = Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first();

    expect($certificate)->not->toBeNull()
        ->and($certificate->unique_code)->not->toBeNull()
        ->and($certificate->user_id)->toBe($user->id)
        ->and($certificate->course_id)->toBe($course->id)
        ->and($enrollment->fresh()->completed_at)->not->toBeNull();
});

test('the Job is idempotent and does not create a duplicate certificate', function () {
    Queue::fake();
    $user = User::factory()->create();
    [$course] = makeCourseWithModules(1);
    completedEnrollment($user, $course);

    (new GenerateCertificateJob($user, $course))->handle();
    (new GenerateCertificateJob($user, $course))->handle();

    expect(Certificate::where('user_id', $user->id)->where('course_id', $course->id)->count())->toBe(1)
        ->and($user->certificates()->where('course_id', $course->id)->count())->toBe(1);
});

test('the Job generates a unique code per certificate', function () {
    Queue::fake();
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    [$course] = makeCourseWithModules(1);
    completedEnrollment($userA, $course);
    completedEnrollment($userB, $course);

    (new GenerateCertificateJob($userA, $course))->handle();
    (new GenerateCertificateJob($userB, $course))->handle();

    $codes = Certificate::where('course_id', $course->id)->pluck('unique_code');

    expect($codes->count())->toBe(2)
        ->and($codes->unique()->count())->toBe(2);
});

test('the full flow goes through a Queue Job when reaching 100% progress', function () {
    Queue::fake();
    $user = User::factory()->create();
    $user->addRole('learner');
    [$course, $section, $modules] = makeCourseWithModules(2);
    completedEnrollment($user, $course);

    foreach ($modules as $module) {
        $this->actingAs($user)->post(route('learner.progress.store', $module));
    }

    Queue::assertPushed(GenerateCertificateJob::class, fn (GenerateCertificateJob $job) => $job->user->id === $user->id && $job->course->id === $course->id);
});

test('reaching 100% progress still produces a certificate in the sync test connection', function () {
    $user = User::factory()->create();
    $user->addRole('learner');
    [$course, $section, $modules] = makeCourseWithModules(2);
    $enrollment = completedEnrollment($user, $course);

    foreach ($modules as $module) {
        $this->actingAs($user)->post(route('learner.progress.store', $module));
    }

    $certificate = Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first();

    expect($certificate)->not->toBeNull()
        ->and($certificate->unique_code)->not->toBeNull()
        ->and($user->certificates()->where('course_id', $course->id)->count())->toBe(1)
        ->and($enrollment->fresh()->completed_at)->not->toBeNull();
});
