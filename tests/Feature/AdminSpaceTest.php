<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->addRole('admin');

    $this->learner = User::factory()->create();
    $this->learner->addRole('learner');
});

test('admin can access the admin dashboard', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Utilisateurs');
});

test('learner cannot access the admin dashboard', function () {
    $this->actingAs($this->learner)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('instructor cannot access admin routes', function () {
    $instructor = User::factory()->create();
    $instructor->addRole('instructor');

    $this->actingAs($instructor)
        ->get(route('admin.courses.index'))
        ->assertForbidden();
});

test('admin can create a category', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.categories.store'), ['name' => 'Développement web'])
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::where('name', 'Développement web')->exists())->toBeTrue();
});

test('admin can update a category', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.categories.update', $category), ['name' => 'Nouveau nom'])
        ->assertRedirect();

    expect($category->refresh()->name)->toBe('Nouveau nom');
});

test('admin cannot delete a category with associated courses', function () {
    $category = Category::factory()->create();
    Course::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Category::find($category->id))->not->toBeNull();
});

test('admin can delete an empty category', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect();

    expect(Category::find($category->id))->toBeNull();
});

test('admin can change a user role via Laratrust', function () {
    $target = User::factory()->create();
    $target->addRole('learner');

    $this->actingAs($this->admin)
        ->patch(route('admin.users.role', $target), ['role' => 'instructor'])
        ->assertRedirect();

    expect($target->refresh()->hasRole('instructor'))->toBeTrue()
        ->and($target->hasRole('learner'))->toBeFalse();
});

test('admin cannot demote themselves', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.users.role', $this->admin), ['role' => 'learner'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($this->admin->hasRole('admin'))->toBeTrue();
});

test('admin cannot delete their own account', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->admin))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(User::find($this->admin->id))->not->toBeNull();
});

test('admin can delete another user', function () {
    $target = User::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $target))
        ->assertRedirect();

    expect(User::find($target->id))->toBeNull();
});

test('admin can publish and unpublish a course', function () {
    $course = Course::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->patch(route('admin.courses.publish', $course))
        ->assertRedirect();

    expect($course->refresh()->status)->toBe('published')
        ->and($course->published_at)->not->toBeNull();

    $this->actingAs($this->admin)
        ->patch(route('admin.courses.unpublish', $course))
        ->assertRedirect();

    expect($course->refresh()->status)->toBe('draft')
        ->and($course->published_at)->toBeNull();
});

test('admin can filter courses by status', function () {
    Course::factory()->published()->create();
    Course::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.courses.index', ['status' => 'published']))
        ->assertOk();
});