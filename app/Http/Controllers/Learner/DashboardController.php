<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Learner overview: enrolled courses, progression and certificates.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $progress = app(ProgressService::class);

        $courses = $user->enrollments()
            ->with(['course.category', 'course.instructor', 'course.sections.modules'])
            ->latest('enrolled_at')
            ->get()
            ->map(fn ($enrollment) => (object) [
                'course' => $enrollment->course,
                'completed_at' => $enrollment->completed_at,
                'percent' => $progress->percent($user, $enrollment->course),
            ]);

        $inProgress = $courses->filter(fn ($item) => $item->percent < 100);
        $completed = $courses->filter(fn ($item) => $item->percent >= 100);
        $certificates = $user->certificates()->with('course.instructor')->latest('issued_at')->get();

        return view('dashboard', [
            'courses' => $courses,
            'inProgress' => $inProgress,
            'completed' => $completed,
            'certificates' => $certificates,
        ]);
    }
}
