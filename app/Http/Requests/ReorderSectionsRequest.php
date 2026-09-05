<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin') || $this->user()->hasRole('instructor');
    }

    public function rules(): array
    {
        return [
            'sections' => ['required', 'array'],
            'sections.*' => ['required', 'integer', 'exists:sections,id'],
        ];
    }
}