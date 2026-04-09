<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuspiciousEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', Rule::in(array_keys(config('anticheat.event_points')))],
            'screenshot' => ['nullable', 'string'],
            'occurred_at' => ['required', 'date'],
        ];
    }
}
