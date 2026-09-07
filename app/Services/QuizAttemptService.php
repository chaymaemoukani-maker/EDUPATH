<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

class QuizAttemptService
{
    public function attemptsUsed(User $user, Quiz $quiz): int
    {
        return $user->quizAttempts()->where('quiz_id', $quiz->id)->count();
    }

    public function attemptsRemaining(User $user, Quiz $quiz): int
    {
        return max(0, (int) $quiz->max_attempts - $this->attemptsUsed($user, $quiz));
    }

    public function canAttempt(User $user, Quiz $quiz): bool
    {
        return $this->attemptsRemaining($user, $quiz) > 0
            && ! $user->quizAttempts()->where('quiz_id', $quiz->id)->where('passed', true)->exists();
    }

    /**
     * Grade the submitted answers, record the attempt (score as a percentage)
     * and mark the associated module completed when passed.
     */
    public function grade(User $user, Quiz $quiz, array $answers): QuizAttempt
    {
        $questions = $quiz->questions()->with('answers')->get();
        $total = $questions->count();
        $correct = 0;

        foreach ($questions as $question) {
            $correctAnswer = $question->answers->firstWhere('is_correct', true);

            if ($correctAnswer && (int) ($answers[$question->id] ?? null) === $correctAnswer->id) {
                $correct++;
            }
        }

        $score = $total > 0 ? (int) round($correct / $total * 100) : 0;
        $passed = $total > 0 && $score >= (int) $quiz->pass_score;

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'passed' => $passed,
            'attempted_at' => now(),
        ]);

        if ($passed) {
            app(ProgressService::class)->markModuleCompleted($user, $quiz->module);
        }

        return $attempt;
    }
}