<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

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
}
