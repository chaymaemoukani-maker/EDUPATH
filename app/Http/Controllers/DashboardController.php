<?php

namespace App\Http\Controllers;

use App\Services\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Role-aware dashboard. Admin and instructor are redirected to their
     * dedicated space; learners get a personalised learning dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('instructor')) {
            return redirect()->route('instructor.courses.index');
        }

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