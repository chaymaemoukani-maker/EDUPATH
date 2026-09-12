<?php

namespace App\Http\Controllers\Learner;

use App\Exceptions\AiAssistantException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AskAiAssistantRequest;
use App\Models\Course;
use App\Services\AiAssistantService;
use App\Services\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    public function __construct(private readonly AiAssistantService $assistant, private readonly ProgressService $progress) {}

    /**
     * AI assistant page: enrolled courses selector + conversation.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $courses = $user->enrollments()
            ->with(['course.category', 'course.instructor', 'course.sections.modules'])
            ->latest('enrolled_at')
            ->get()
            ->map(fn ($enrollment) => (object) [
                'course' => $enrollment->course,
                'percent' => $this->progress->percent($user, $enrollment->course),
            ]);

        $defaultCourse = $courses->sortByDesc('percent')->first();

        $requestedId = $request->integer('course');
        $selected = $courses->first(fn ($item) => $item->course->id === $requestedId) ?? $defaultCourse;

        $conversation = $selected
            ? session('learner_ai_conversation.'.$selected->course->id, [])
            : [];

        return view('learner.ai-assistant.index', [
            'courses' => $courses,
            'conversation' => $conversation,
            'selectedCourseId' => $selected?->course->id,
        ]);
    }

    /**
     * Send a question to the AI tutor and store the short conversation.
     */
    public function ask(AskAiAssistantRequest $request): RedirectResponse
    {
        $course = Course::findOrFail($request->integer('course_id'));

        $this->authorize('learn', $course);

        $key = 'learner_ai_conversation.'.$course->id;
        $conversation = session($key, []);

        try {
            $answer = $this->assistant->ask($request->user(), $course, $request->string('question')->toString(), $conversation);
        } catch (AiAssistantException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        $conversation[] = ['role' => 'user', 'content' => $request->string('question')->toString()];
        $conversation[] = ['role' => 'assistant', 'content' => $answer];

        // Keep only the last 6 exchanges to bound context and memory.
        $conversation = array_slice($conversation, -12);

        session([$key => $conversation]);

        return redirect()->route('learner.ai-assistant.index', ['course' => $course->id])
            ->with('success', 'Réponse générée.');
    }
}
