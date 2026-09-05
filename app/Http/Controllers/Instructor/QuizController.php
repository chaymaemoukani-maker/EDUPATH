<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuizController extends Controller
{
    /**
     * Show the quiz editor for a module (create or edit).
     */
    public function edit(Module $module): View
    {
        $this->authorize('update', $module);

        $module->load('quiz.questions.answers');

        return view('instructor.quizzes.edit', compact('module'));
    }

    /**
     * Store a quiz for a module (one quiz per module).
     */
    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $this->authorize('create', Quiz::class);
        $this->authorize('update', Module::findOrFail($request->validated('module_id')));

        $module = Module::findOrFail($request->validated('module_id'));

        if ($module->quiz) {
            return redirect()
                ->route('instructor.quizzes.edit', $module)
                ->with('error', 'Ce module possède déjà un quiz.');
        }

        $quiz = Quiz::create([
            'module_id' => $module->id,
            'title' => $request->validated('title'),
            'pass_score' => $request->validated('pass_score'),
            'max_attempts' => $request->validated('max_attempts'),
        ]);

        $this->syncQuestions($quiz, $request->validated('questions'));

        return redirect()
            ->route('instructor.quizzes.edit', $module)
            ->with('success', 'Quiz créé avec succès.');
    }

    /**
     * Update the specified quiz, replacing its questions and answers.
     */
    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $quiz->update([
            'title' => $request->validated('title'),
            'pass_score' => $request->validated('pass_score'),
            'max_attempts' => $request->validated('max_attempts'),
        ]);

        $quiz->questions()->delete();
        $this->syncQuestions($quiz, $request->validated('questions'));

        return redirect()
            ->route('instructor.quizzes.edit', $quiz->module_id)
            ->with('success', 'Quiz mis à jour avec succès.');
    }

    /**
     * Replace all questions/answers of a quiz from validated form data.
     */
    private function syncQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as $questionData) {
            $question = \App\Models\Question::create([
                'quiz_id' => $quiz->id,
                'text' => $questionData['text'],
            ]);

            foreach ($questionData['answers'] as $index => $answerText) {
                \App\Models\Answer::create([
                    'question_id' => $question->id,
                    'text' => $answerText,
                    'is_correct' => (int) $questionData['correct_answer'] === $index,
                ]);
            }
        }
    }
}