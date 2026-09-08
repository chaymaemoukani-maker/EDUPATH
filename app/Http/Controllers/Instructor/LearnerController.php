<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearnerController extends Controller
{
    /**
     * Learners enrolled in the authenticated instructor's courses.
     * Only enrollments for courses owned by the instructor are shown.
     */
    public function index(Request $request): View
    {
        $instructorId = auth()->id();

        $courses = Course::with('category')
            ->where('instructor_id', $instructorId)
            ->orderBy('title')
            ->get();

        $course = null;
        $query = \App\Models\Enrollment::query()
            ->whereHas('course', fn ($q) => $q->where('instructor_id', $instructorId))
            ->with(['user', 'course:id,title']);

        if ($request->filled('course')) {
            $course = Course::findOrFail($request->integer('course'));

            // The course must belong to the instructor (policy enforces ownership).
            $this->authorize('update', $course);

            $query->where('course_id', $course->id);
        }

        $progress = app(ProgressService::class);

        $items = $query
            ->with(['course.sections.modules'])
            ->latest('enrolled_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($enrollment) => (object) [
                'learner' => $enrollment->user,
                'course' => $enrollment->course,
                'enrolled_at' => $enrollment->enrolled_at,
                'completed_at' => $enrollment->completed_at,
                'percent' => $progress->percent($enrollment->user, $enrollment->course),
            ]);

        return view('instructor.learners.index', compact('courses', 'course', 'items'));
    }
}
