<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display a listing of all courses.
     */
    public function index(Request $request): View
    {
        $query = Course::with(['instructor', 'category'])
            ->withCount('enrollments');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $courses = $query->latest()->paginate(15)->withQueryString();

        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Publish a course. Admin-only action.
     */
    public function publish(Course $course): RedirectResponse
    {
        $this->authorize('publish', $course);

        $course->update([
            'status' => 'published',
            'published_at' => $course->published_at ?? now(),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Cours publié avec succès.');
    }

    /**
     * Unpublish a course. Admin-only action.
     */
    public function unpublish(Course $course): RedirectResponse
    {
        $this->authorize('publish', $course);

        $course->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Cours dépublié.');
    }
}