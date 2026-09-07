<?php

namespace App\Services;

use App\Events\CourseCompleted;
use App\Models\Course;
use App\Models\Module;
use App\Models\Progress;
use App\Models\User;

class ProgressService
{
    public function totalModules(Course $course): int
    {
        if ($course->relationLoaded('sections')) {
            return $course->sections->sum(fn ($section) => $section->relationLoaded('modules')
                ? $section->modules->count()
                : $section->modules()->count());
        }

        return (int) $course->sections()->withCount('modules')->pluck('modules_count')->sum();
    }

    public function completedModules(User $user, Course $course): int
    {
        $moduleIds = $course->relationLoaded('sections')
            ? $course->sections->flatMap(fn ($section) => $section->relationLoaded('modules')
                ? $section->modules->pluck('id')
                : $section->modules()->pluck('id'))
            : $course->sections()->with('modules')->get()
                ->flatMap(fn ($section) => $section->modules->pluck('id'));

        if ($moduleIds->isEmpty()) {
            return 0;
        }

        return $user->progress()->whereIn('module_id', $moduleIds)->count();
    }

    /**
     * Percentage = completed modules / total modules of the course.
     */
    public function percent(User $user, Course $course): int
    {
        $total = $this->totalModules($course);

        if ($total === 0) {
            return 0;
        }

        return (int) round($this->completedModules($user, $course) / $total * 100);
    }

    public function isCompleted(User $user, Course $course): bool
    {
        $total = $this->totalModules($course);

        return $total > 0 && $this->completedModules($user, $course) >= $total;
    }

    /**
     * Create the progress line for a completed module and dispatch
     * CourseCompleted when the course reaches 100%.
     */
    public function markModuleCompleted(User $user, Module $module): bool
    {
        if ($user->progress()->where('module_id', $module->id)->exists()) {
            return false;
        }

        Progress::create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'completed_at' => now(),
        ]);

        $course = $module->section?->course;

        if ($course && $this->isCompleted($user, $course)) {
            CourseCompleted::dispatch($user, $course);
        }

        return true;
    }
}