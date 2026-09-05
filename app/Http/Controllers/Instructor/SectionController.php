<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderSectionsRequest;
use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;

class SectionController extends Controller
{
    /**
     * Store a newly created section for a course.
     */
    public function store(StoreSectionRequest $request): RedirectResponse
    {
        $this->authorize('create', Section::class);
        $this->authorize('update', Course::findOrFail($request->validated('course_id')));

        $course = Course::findOrFail($request->validated('course_id'));
        $order = ($course->sections()->max('order') ?? 0) + 1;

        Section::create([
            'course_id' => $course->id,
            'title' => $request->validated('title'),
            'order' => $order,
        ]);

        return redirect()
            ->route('instructor.courses.curriculum', $course)
            ->with('success', 'Section ajoutée.');
    }

    /**
     * Update the specified section.
     */
    public function update(UpdateSectionRequest $request, Section $section): RedirectResponse
    {
        $section->update($request->validated());

        return redirect()
            ->route('instructor.courses.curriculum', $section->course_id)
            ->with('success', 'Section mise à jour.');
    }

    /**
     * Remove the specified section (cascade deletes its modules).
     */
    public function destroy(Section $section): RedirectResponse
    {
        $this->authorize('delete', $section);

        $courseId = $section->course_id;
        $section->delete();

        return redirect()
            ->route('instructor.courses.curriculum', $courseId)
            ->with('success', 'Section supprimée.');
    }

    /**
     * Reorder sections by the submitted order of ids.
     */
    public function reorder(ReorderSectionsRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $validIds = $course->sections()->whereIn('id', $request->validated('sections'))->pluck('id')->all();

        foreach ($request->validated('sections') as $index => $id) {
            if (! in_array($id, $validIds)) {
                continue;
            }
            Section::whereKey($id)->update(['order' => $index + 1]);
        }

        return redirect()
            ->route('instructor.courses.curriculum', $course)
            ->with('success', 'Ordre des sections mis à jour.');
    }
}