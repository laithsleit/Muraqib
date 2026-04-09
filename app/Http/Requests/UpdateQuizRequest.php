<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:300'],
            'allow_retake' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'score_threshold' => ['required', 'integer', 'min:1'],
        ];
    }
}
