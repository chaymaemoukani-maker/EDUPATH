<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('module'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['text', 'video', 'pdf'])],
            'content' => $this->contentRules(),
        ];
    }

    protected function contentRules(): array
    {
        if ($this->input('type') === 'pdf' && ! $this->hasFile('content')) {
            return ['nullable'];
        }

        $rules = ['required'];

        if ($this->input('type') === 'text') {
            $rules[] = 'string';
        } elseif ($this->input('type') === 'video') {
            $rules[] = 'url';
        } else {
            $rules[] = 'file';
            $rules[] = 'mimes:pdf';
            $rules[] = 'max:10240';
        }

        return $rules;
    }
}