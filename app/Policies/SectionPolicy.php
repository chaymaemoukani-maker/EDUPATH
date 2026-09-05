<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Section $section): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('instructor');
    }

    public function update(User $user, Section $section): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor') && $section->course && $section->course->instructor_id === $user->id;
    }

    public function delete(User $user, Section $section): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor') && $section->course && $section->course->instructor_id === $user->id;
    }
}
