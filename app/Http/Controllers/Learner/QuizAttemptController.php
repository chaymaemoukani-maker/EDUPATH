<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learner\StoreQuizAttemptRequest;
use App\Models\Quiz;
use App\Services\QuizAttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuizAttemptController extends Controller
{
    /**
     * Quiz taking page: header (questions, min score, remaining attempts)
     * and the inline result of the last attempt.
     */
    public function show(Quiz $quiz): View
    {
        $course = $quiz->module?->section?->course;
        abort_if($course === null, 404);

        $this->authorize('learn', $course);

        $user = auth()->user();
        $service = app(QuizAttemptService::class);

        $quiz->load(['questions.answers', 'module.section.course']);

        return view('learner.quizzes.show', [
            'quiz' => $quiz,
            'course' => $course,
            'passedAlready' => $user->quizAttempts()->where('quiz_id', $quiz->id)->where('passed', true)->exists(),
            'attemptsUsed' => $service->attemptsUsed($user, $quiz),
            'attemptsRemaining' => $service->attemptsRemaining($user, $quiz),
            'lastAttempt' => $user->quizAttempts()->where('quiz_id', $quiz->id)->latest('attempted_at')->first(),
            'moduleCompleted' => $quiz->module->progress()->where('user_id', $user->id)->exists(),
        ]);
    }

    /**
     * Submit answers and record a new attempt.
     */
    public function store(StoreQuizAttemptRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('take', $quiz);

        $attempt = app(QuizAttemptService::class)->grade(
            $request->user(),
            $quiz,
            $request->validated('answers', []),
        );

        return redirect()
            ->route('learner.quizzes.show', $quiz)
            ->with(
                $attempt->passed ? 'success' : 'error',
                $attempt->passed
                    ? "Quiz réussi avec {$attempt->score} % !"
                    : "Quiz échoué — {$attempt->score} %."
            );
    }
}