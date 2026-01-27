<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class StoreStreamsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->activeSchool !== null;
    }

    public function rules(): array
    {
        return [
            'streams' => ['required', 'array', 'min:1'],
            'streams.*.name' => ['required', 'string', 'max:100', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'streams.required' => 'At least one stream is required',
            'streams.min' => 'At least one stream is required',
            'streams.*.name.required' => 'Stream name is required',
            'streams.*.name.max' => 'Stream name cannot exceed 100 characters',
            'streams.*.name.distinct' => 'Duplicate stream names are not allowed',
        ];
    }
}
