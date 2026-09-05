<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin') || $this->user()->hasRole('instructor');
    }

    public function rules(): array
    {
        return [
            'section_id' => ['required', 'exists:sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['text', 'video', 'pdf'])],
            'content' => ['required', $this->contentRules()],
        ];
    }

    protected function contentRules(): array
    {
        return match ($this->input('type')) {
            'text' => ['string'],
            'video' => ['url'],
            'pdf' => ['file', 'mimes:pdf', 'max:10240'],
            default => ['string'],
        };
    }
}