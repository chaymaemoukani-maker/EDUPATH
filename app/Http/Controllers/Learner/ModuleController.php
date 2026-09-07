<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\ProgressService;
use Illuminate\View\View;

class ModuleController extends Controller
{
    /**
     * Display a module content for an enrolled learner.
     */
    public function show(Module $module): View
    {
        $this->authorize('learn', $module);

        $user = auth()->user();

        abort_if(! $module->section || ! $module->section->course, 404);

        $course = $module->section->course;
        $course->load(['category', 'instructor', 'sections.modules.quiz']);

        $completed = $module->progress()->where('user_id', $user->id)->exists();
        $percent = app(ProgressService::class)->percent($user, $course);
        $certificate = $course->certificates()->where('user_id', $user->id)->first();
        $quiz = $module->quiz;

        return view('learner.modules.show', compact('module', 'course', 'completed', 'percent', 'certificate', 'quiz'));
    }
}