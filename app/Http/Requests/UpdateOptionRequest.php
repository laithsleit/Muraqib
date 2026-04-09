<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'option_text' => ['required', 'string', 'max:500'],
            'is_correct' => ['nullable', 'boolean'],
        ];
    }
}
