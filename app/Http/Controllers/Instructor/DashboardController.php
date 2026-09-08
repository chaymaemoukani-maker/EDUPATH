<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Instructor overview: course stats and recent courses.
     */
    public function index(): View
    {
        $totalCourses = Course::where('instructor_id', auth()->id())->count();
        $publishedCourses = Course::where('instructor_id', auth()->id())->where('status', 'published')->count();
        $draftCourses = Course::where('instructor_id', auth()->id())->where('status', 'draft')->count();
        $totalLearners = Course::where('instructor_id', auth()->id())
            ->withCount('enrollments')
            ->get()
            ->sum('enrollments_count');

        $recentCourses = Course::with('category')
            ->withCount('enrollments')
            ->where('instructor_id', auth()->id())
            ->latest()
            ->limit(5)
            ->get();

        return view('instructor.dashboard', compact(
            'totalCourses',
            'publishedCourses',
            'draftCourses',
            'totalLearners',
            'recentCourses'
        ));
    }
}
