<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\ProgressService;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * List the courses the learner is enrolled in, with progression.
     */
    public function index(): View
    {
        $user = auth()->user();
        $progress = app(ProgressService::class);

        $items = $user->enrollments()
            ->with(['course.category', 'course.instructor', 'course.sections.modules'])
            ->latest('enrolled_at')
            ->get()
            ->map(fn ($enrollment) => (object) [
                'course' => $enrollment->course,
                'percent' => $progress->percent($user, $enrollment->course),
            ]);

        return view('learner.courses.index', compact('items'));
    }

    /**
     * Course player: curriculum with per-module state and global progress.
     */
    public function show(Course $course): View
    {
        $this->authorize('learn', $course);

        $user = auth()->user();

        $course->load(['category', 'instructor', 'sections.modules.quiz']);

        $completedModuleIds = $user->progress()->pluck('module_id')->all();
        $percent = app(ProgressService::class)->percent($user, $course);
        $certificate = $course->certificates()->where('user_id', $user->id)->first();

        return view('learner.courses.show', compact('course', 'completedModuleIds', 'percent', 'certificate'));
    }
}