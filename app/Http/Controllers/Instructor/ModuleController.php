<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Models\Module;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;

class ModuleController extends Controller
{
    /**
     * Store a newly created module for a section.
     */
    public function store(StoreModuleRequest $request): RedirectResponse
    {
        $this->authorize('create', Module::class);
        $this->authorize('update', Section::findOrFail($request->validated('section_id')));

        $section = Section::findOrFail($request->validated('section_id'));
        $order = ($section->modules()->max('order') ?? 0) + 1;

        Module::create([
            'section_id' => $section->id,
            'title' => $request->validated('title'),
            'type' => $request->validated('type'),
            'content' => $this->storeContent($request->validated('type'), $request->validated('content')),
            'order' => $order,
        ]);

        return redirect()
            ->route('instructor.courses.curriculum', $section->course_id)
            ->with('success', 'Module ajouté.');
    }

    /**
     * Update the specified module.
     */
    public function update(UpdateModuleRequest $request, Module $module): RedirectResponse
    {
        $payload = [
            'title' => $request->validated('title'),
            'type' => $request->validated('type'),
        ];

        if ($request->validated('type') === 'pdf' && ! $request->hasFile('content')) {
            $payload['content'] = $module->content;
        } else {
            $payload['content'] = $this->storeContent($request->validated('type'), $request->validated('content'));
        }

        $module->update($payload);

        return redirect()
            ->route('instructor.courses.curriculum', $module->section->course_id)
            ->with('success', 'Module mis à jour.');
    }

    /**
     * Remove the specified module.
     */
    public function destroy(Module $module): RedirectResponse
    {
        $this->authorize('delete', $module);

        $courseId = $module->section->course_id;
        $module->delete();

        return redirect()
            ->route('instructor.courses.curriculum', $courseId)
            ->with('success', 'Module supprimé.');
    }

    /**
     * Persist module content: text/video stored as-is, PDF uploaded to public disk.
     */
    private function storeContent(string $type, mixed $content): string
    {
        if ($type === 'pdf' && $content instanceof \Illuminate\Http\UploadedFile) {
            return $content->store('modules', 'public');
        }

        return (string) $content;
    }
}