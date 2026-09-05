<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('quiz'));
    }

    protected function prepareForValidation(): void
    {
        $questions = json_decode((string) $this->input('questions_json', ''), true);

        if (is_array($questions)) {
            $this->merge(['questions' => $questions]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'pass_score' => ['required', 'integer', 'between:0,100'],
            'max_attempts' => ['required', 'integer', 'between:1,10'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.text' => ['required', 'string'],
            'questions.*.answers' => ['required', 'array', 'min:2', 'max:6'],
            'questions.*.answers.*' => ['required', 'string'],
            'questions.*.correct_answer' => ['required', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('questions', []) as $index => $question) {
                $answersCount = count($question['answers'] ?? []);
                $correct = $question['correct_answer'] ?? -1;

                if ($correct < 0 || $correct >= $answersCount) {
                    $validator->errors()->add("questions.$index.correct_answer", 'La réponse correcte doit référencer une réponse valide.');
                }
            }
        });
    }
}