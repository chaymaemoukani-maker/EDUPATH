<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\ModuleController;
use App\Http\Controllers\Instructor\QuizController;
use App\Http\Controllers\Instructor\SectionController;
use App\Http\Controllers\Learner\CertificateController as LearnerCertificateController;
use App\Http\Controllers\Learner\CourseController as LearnerCourseController;
use App\Http\Controllers\Learner\EnrollmentController;
use App\Http\Controllers\Learner\ModuleController as LearnerModuleController;
use App\Http\Controllers\Learner\ProgressController;
use App\Http\Controllers\Learner\QuizAttemptController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * Admin space — role:admin (Laratrust). Publication of courses is admin-only.
 */
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class)
        ->except(['show', 'create', 'edit'])
        ->names('categories');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/courses', [AdminCourseController::class, 'index'])->name('courses.index');
    Route::patch('/courses/{course}/publish', [AdminCourseController::class, 'publish'])->name('courses.publish');
    Route::patch('/courses/{course}/unpublish', [AdminCourseController::class, 'unpublish'])->name('courses.unpublish');
});

/**
 * Instructor space — role:instructor. Course publication is never exposed here.
 */
Route::middleware(['auth', 'verified', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::resource('courses', CourseController::class)
        ->except(['show'])
        ->names('courses');
    Route::get('/courses/{course}/curriculum', [CourseController::class, 'curriculum'])->name('courses.curriculum');

    Route::post('/courses/{course}/sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');

    Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
    Route::patch('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

    Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::patch('/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');

    Route::get('/modules/{module}/quiz', [QuizController::class, 'edit'])->name('quizzes.edit');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::patch('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
});

/**
 * Public catalog — published courses only (CoursePolicy::view guards the detail page).
 */
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/catalog/{course}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/verify', [CertificateController::class, 'verify'])
    ->middleware('throttle:verify-certificate')
    ->name('verify');

/**
 * Learner space — role:learner (Laratrust). Enrollment, progression, quizzes, certificates.
 */
Route::middleware(['auth', 'verified', 'role:learner'])->prefix('learner')->name('learner.')->group(function () {
    Route::get('/courses', [LearnerCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [LearnerCourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('enrollments.store');

    Route::get('/modules/{module}', [LearnerModuleController::class, 'show'])->name('modules.show');
    Route::post('/modules/{module}/complete', [ProgressController::class, 'store'])->name('progress.store');

    Route::get('/quizzes/{quiz}', [QuizAttemptController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quiz}', [QuizAttemptController::class, 'store'])->name('quizzes.store');

    Route::get('/certificates', [LearnerCertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}/download', [LearnerCertificateController::class, 'download'])->name('certificates.download');
});

require __DIR__.'/auth.php';