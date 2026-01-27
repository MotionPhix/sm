<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->activeSchool !== null;
    }

    public function rules(): array
    {
        return [
            'classes' => ['required', 'array', 'min:1'],
            'classes.*.name' => ['required', 'string', 'max:100', 'distinct'],
            'classes.*.order' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'classes.required' => 'At least one class is required',
            'classes.min' => 'At least one class is required',
            'classes.*.name.required' => 'Class name is required',
            'classes.*.name.max' => 'Class name cannot exceed 100 characters',
            'classes.*.name.distinct' => 'Duplicate class names are not allowed',
            'classes.*.order.required' => 'Class order is required',
            'classes.*.order.integer' => 'Class order must be a number',
            'classes.*.order.min' => 'Class order must be at least 1',
        ];
    }
}
