<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;
use App\Services\QuizAttemptService;

class QuizPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Quiz $quiz): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('instructor');
    }

    public function update(User $user, Quiz $quiz): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor')
            && $quiz->module
            && $quiz->module->section
            && $quiz->module->section->course
            && $quiz->module->section->course->instructor_id === $user->id;
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor')
            && $quiz->module
            && $quiz->module->section
            && $quiz->module->section->course
            && $quiz->module->section->course->instructor_id === $user->id;
    }

    public function take(User $user, Quiz $quiz): bool
    {
        if (! $quiz->module || ! $quiz->module->section || ! $quiz->module->section->course) {
            return false;
        }

        $course = $quiz->module->section->course;

        return $course->enrollments()->where('user_id', $user->id)->exists()
            && app(QuizAttemptService::class)->canAttempt($user, $quiz);
    }
}
