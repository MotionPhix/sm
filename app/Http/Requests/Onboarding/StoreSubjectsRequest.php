<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->activeSchool !== null;
    }

    public function rules(): array
    {
        return [
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*.name' => ['required', 'string', 'max:100', 'distinct'],
            'subjects.*.code' => ['required', 'string', 'max:20', 'distinct'],
            'bypass_password' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'subjects.required' => 'At least one subject is required',
            'subjects.min' => 'At least one subject is required',
            'subjects.*.name.required' => 'Subject name is required',
            'subjects.*.name.max' => 'Subject name cannot exceed 100 characters',
            'subjects.*.name.distinct' => 'Duplicate subject names are not allowed',
            'subjects.*.code.required' => 'Subject code is required',
            'subjects.*.code.max' => 'Subject code cannot exceed 20 characters',
            'subjects.*.code.distinct' => 'Duplicate subject codes are not allowed',
        ];
    }
}
