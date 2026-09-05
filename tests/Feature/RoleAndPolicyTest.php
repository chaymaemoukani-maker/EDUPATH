<?php

use App\Models\Category;
use App\Models\Course;
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
});

test('admin user has admin role', function () {
    $admin = User::where('email', 'admin@example.com')->first();
    expect($admin->hasRole('admin'))->toBeTrue();
});

test('instructor user has instructor role', function () {
    $instructor = User::where('email', 'instructor@example.com')->first();
    expect($instructor->hasRole('instructor'))->toBeTrue();
});

test('learner user has learner role', function () {
    $learner = User::where('email', 'learner@example.com')->first();
    expect($learner->hasRole('learner'))->toBeTrue();
});

test('admin user has administrative permissions', function () {
    $admin = User::where('email', 'admin@example.com')->first();
    expect($admin->isAbleTo('manage-users'))->toBeTrue()
        ->and($admin->isAbleTo('manage-roles'))->toBeTrue()
        ->and($admin->isAbleTo('publish-courses'))->toBeTrue();
});

test('instructor user has course creation permissions but not user management', function () {
    $instructor = User::where('email', 'instructor@example.com')->first();
    expect($instructor->isAbleTo('create-courses'))->toBeTrue()
        ->and($instructor->isAbleTo('manage-users'))->toBeFalse()
        ->and($instructor->isAbleTo('publish-courses'))->toBeFalse();
});

test('learner user has learning permissions but cannot create or update courses', function () {
    $learner = User::where('email', 'learner@example.com')->first();
    expect($learner->isAbleTo('enroll-courses'))->toBeTrue()
        ->and($learner->isAbleTo('create-courses'))->toBeFalse()
        ->and($learner->isAbleTo('update-courses'))->toBeFalse()
        ->and($learner->isAbleTo('delete-courses'))->toBeFalse();
});

test('learner cannot create a course', function () {
    $learner = User::where('email', 'learner@example.com')->first();
    expect($learner->can('create', Course::class))->toBeFalse();
});

test('learner cannot update a course', function () {
    $learner = User::where('email', 'learner@example.com')->first();
    $instructor = User::where('email', 'instructor@example.com')->first();
    $category = Category::create(['name' => 'Test Cat 1', 'slug' => 'test-cat-1']);
    $course = Course::create([
        'instructor_id' => $instructor->id,
        'category_id' => $category->id,
        'title' => 'Sample Course 1',
        'description' => 'Sample Desc',
    ]);
    expect($learner->can('update', $course))->toBeFalse();
});

test('learner cannot delete a course', function () {
    $learner = User::where('email', 'learner@example.com')->first();
    $instructor = User::where('email', 'instructor@example.com')->first();
    $category = Category::create(['name' => 'Test Cat 2', 'slug' => 'test-cat-2']);
    $course = Course::create([
        'instructor_id' => $instructor->id,
        'category_id' => $category->id,
        'title' => 'Sample Course 2',
        'description' => 'Sample Desc',
    ]);
    expect($learner->can('delete', $course))->toBeFalse();
});

test('instructor can update their own course', function () {
    $instructor = User::where('email', 'instructor@example.com')->first();
    $category = Category::create(['name' => 'Web Dev', 'slug' => 'web-dev']);
    $course = Course::create([
        'instructor_id' => $instructor->id,
        'category_id' => $category->id,
        'title' => 'Laravel 101',
        'description' => 'Intro to Laravel',
    ]);

    expect($instructor->can('update', $course))->toBeTrue();
});

test('instructor can delete their own course', function () {
    $instructor = User::where('email', 'instructor@example.com')->first();
    $category = Category::create(['name' => 'Data Science', 'slug' => 'data-science']);
    $course = Course::create([
        'instructor_id' => $instructor->id,
        'category_id' => $category->id,
        'title' => 'Python 101',
        'description' => 'Intro to Python',
    ]);

    expect($instructor->can('delete', $course))->toBeTrue();
});

test('instructor cannot update another instructor course', function () {
    $instructor1 = User::where('email', 'instructor@example.com')->first();
    $instructor2 = User::create([
        'name' => 'Instructor Two',
        'email' => 'instructor2@example.com',
        'password' => bcrypt('password'),
    ]);
    $instructor2->addRole('instructor');

    $category = Category::create(['name' => 'Mobile Dev', 'slug' => 'mobile-dev']);
    $course = Course::create([
        'instructor_id' => $instructor2->id,
        'category_id' => $category->id,
        'title' => 'Flutter 101',
        'description' => 'Intro to Flutter',
    ]);

    expect($instructor1->can('update', $course))->toBeFalse();
});

test('instructor cannot delete another instructor course', function () {
    $instructor1 = User::where('email', 'instructor@example.com')->first();
    $instructor2 = User::create([
        'name' => 'Instructor Two',
        'email' => 'instructor2@example.com',
        'password' => bcrypt('password'),
    ]);
    $instructor2->addRole('instructor');

    $category = Category::create(['name' => 'DevOps', 'slug' => 'devops']);
    $course = Course::create([
        'instructor_id' => $instructor2->id,
        'category_id' => $category->id,
        'title' => 'Docker 101',
        'description' => 'Intro to Docker',
    ]);

    expect($instructor1->can('delete', $course))->toBeFalse();
});

test('instructor cannot manage sections of a course belonging to another instructor', function () {
    $instructor1 = User::where('email', 'instructor@example.com')->first();
    $instructor2 = User::create([
        'name' => 'Instructor Two',
        'email' => 'instructor2@example.com',
        'password' => bcrypt('password'),
    ]);
    $instructor2->addRole('instructor');

    $category = Category::create(['name' => 'Cloud', 'slug' => 'cloud']);
    $course = Course::create([
        'instructor_id' => $instructor2->id,
        'category_id' => $category->id,
        'title' => 'AWS 101',
        'description' => 'Intro to AWS',
    ]);
    $section = Section::create([
        'course_id' => $course->id,
        'title' => 'Section 1',
        'order' => 1,
    ]);

    expect($instructor1->can('update', $section))->toBeFalse()
        ->and($instructor1->can('delete', $section))->toBeFalse();
});

test('instructor cannot manage modules of another instructor course', function () {
    $instructor1 = User::where('email', 'instructor@example.com')->first();
    $instructor2 = User::create([
        'name' => 'Instructor Two',
        'email' => 'instructor2@example.com',
        'password' => bcrypt('password'),
    ]);
    $instructor2->addRole('instructor');

    $category = Category::create(['name' => 'Security', 'slug' => 'security']);
    $course = Course::create([
        'instructor_id' => $instructor2->id,
        'category_id' => $category->id,
        'title' => 'Cybersecurity 101',
        'description' => 'Intro to Security',
    ]);
    $section = Section::create(['course_id' => $course->id, 'title' => 'S1', 'order' => 1]);
    $module = Module::create([
        'section_id' => $section->id,
        'title' => 'Module 1',
        'type' => 'text',
        'content' => 'Sample content',
        'order' => 1,
    ]);

    expect($instructor1->can('update', $module))->toBeFalse()
        ->and($instructor1->can('delete', $module))->toBeFalse();
});

test('instructor cannot manage quiz of another instructor course', function () {
    $instructor1 = User::where('email', 'instructor@example.com')->first();
    $instructor2 = User::create([
        'name' => 'Instructor Two',
        'email' => 'instructor2@example.com',
        'password' => bcrypt('password'),
    ]);
    $instructor2->addRole('instructor');

    $category = Category::create(['name' => 'AI', 'slug' => 'ai']);
    $course = Course::create([
        'instructor_id' => $instructor2->id,
        'category_id' => $category->id,
        'title' => 'ML 101',
        'description' => 'Intro to ML',
    ]);
    $section = Section::create(['course_id' => $course->id, 'title' => 'S1', 'order' => 1]);
    $module = Module::create(['section_id' => $section->id, 'title' => 'M1', 'type' => 'text', 'content' => 'Text', 'order' => 1]);
    $quiz = Quiz::create(['module_id' => $module->id, 'title' => 'Quiz 1', 'pass_score' => 70, 'max_attempts' => 3]);

    expect($instructor1->can('update', $quiz))->toBeFalse()
        ->and($instructor1->can('delete', $quiz))->toBeFalse();
});

test('admin can manage all courses', function () {
    $admin = User::where('email', 'admin@example.com')->first();
    $instructor = User::where('email', 'instructor@example.com')->first();

    $category = Category::create(['name' => 'Design', 'slug' => 'design']);
    $course = Course::create([
        'instructor_id' => $instructor->id,
        'category_id' => $category->id,
        'title' => 'UI/UX Design',
        'description' => 'Figma Basics',
    ]);

    expect($admin->can('update', $course))->toBeTrue()
        ->and($admin->can('delete', $course))->toBeTrue()
        ->and($admin->can('publish', $course))->toBeTrue();
});

test('admin can manage users and user roles', function () {
    $admin = User::where('email', 'admin@example.com')->first();
    $learner = User::where('email', 'learner@example.com')->first();

    expect($admin->can('viewAny', User::class))->toBeTrue()
        ->and($admin->can('updateRole', [$learner, $learner, 'instructor']))->toBeTrue();
});

test('instructor and learner cannot modify user roles', function () {
    $instructor = User::where('email', 'instructor@example.com')->first();
    $learner = User::where('email', 'learner@example.com')->first();

    expect($instructor->can('updateRole', [$learner, $learner, 'admin']))->toBeFalse()
        ->and($learner->can('updateRole', [$learner, $learner, 'instructor']))->toBeFalse();
});
