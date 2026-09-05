<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Course $course): bool
    {
        if ($course->status === 'published') {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') || ($user->hasRole('instructor') && $course->instructor_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('instructor');
    }

    public function update(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor') && $course->instructor_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor') && $course->instructor_id === $user->id;
    }

    public function publish(User $user, Course $course): bool
    {
        return $user->hasRole('admin');
    }
}
