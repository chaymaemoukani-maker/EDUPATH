<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Enroll the authenticated learner into a published course.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('enroll', $course);

        Enrollment::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        return redirect()
            ->route('learner.courses.show', $course)
            ->with('success', 'Inscription confirmée. Bon apprentissage !');
    }
}