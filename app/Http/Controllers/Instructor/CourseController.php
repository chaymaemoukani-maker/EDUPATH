<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display the instructor's courses.
     */
    public function index(Request $request): View
    {
        $courses = Course::with('category', 'sections')
            ->withCount('enrollments')
            ->where('instructor_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('instructor.courses.index', compact('courses'));
    }

    /**
     * Show the course creation form.
     */
    public function create(): View
    {
        $this->authorize('create', Course::class);

        $categories = Category::orderBy('name')->get();

        return view('instructor.courses.create', compact('categories'));
    }

    /**
     * Store a newly created course in draft status.
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = Course::create(array_merge(
            $request->validated(),
            [
                'instructor_id' => auth()->id(),
                'status' => 'draft',
            ]
        ));

        return redirect()
            ->route('instructor.courses.curriculum', $course)
            ->with('success', 'Cours créé. Ajoutez maintenant ses sections et modules.');
    }

    /**
     * Show the course edit form.
     */
    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        $categories = Category::orderBy('name')->get();

        return view('instructor.courses.edit', compact('course', 'categories'));
    }

    /**
     * Update the specified course. Never touches status (publication is admin-only).
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $validated = $request->validated();
        $validated['image'] = empty($validated['image']) ? null : $validated['image'];

        $course->update($validated);

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('success', 'Cours mis à jour avec succès.');
    }

    /**
     * Remove the specified course (cascade deletes sections, modules, quizzes).
     */
    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()
            ->route('instructor.courses.index')
            ->with('success', 'Cours supprimé avec succès.');
    }

    /**
     * Curriculum builder: sections and their modules.
     */
    public function curriculum(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load(['sections' => fn ($query) => $query->orderBy('order')->with(['modules' => fn ($q) => $q->orderBy('order')->with('quiz')])]);

        return view('instructor.courses.curriculum', compact('course'));
    }
}