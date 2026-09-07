<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

class ModulePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Module $module): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('instructor');
    }

    public function update(User $user, Module $module): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor')
            && $module->section
            && $module->section->course
            && $module->section->course->instructor_id === $user->id;
    }

    public function delete(User $user, Module $module): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor')
            && $module->section
            && $module->section->course
            && $module->section->course->instructor_id === $user->id;
    }

    public function learn(User $user, Module $module): bool
    {
        return $module->section
            && $module->section->course
            && $module->section->course->enrollments()->where('user_id', $user->id)->exists();
    }
}
