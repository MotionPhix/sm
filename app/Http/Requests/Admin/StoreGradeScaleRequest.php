<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeScaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('grading.configure');
    }

    public function rules(): array
    {
        $school = $this->user()->activeSchool;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('grade_scales')->where('school_id', $school->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.min_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'steps.*.max_percent' => ['required', 'integer', 'min:0', 'max:100', 'gte:steps.*.min_percent'],
            'steps.*.grade' => ['required', 'string', 'max:4'],
            'steps.*.comment' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Scale name is required.',
            'name.unique' => 'A grading scale with this name already exists.',
            'steps.required' => 'At least one grade step is required.',
            'steps.min' => 'At least one grade step is required.',
            'steps.*.min_percent.required' => 'Minimum percentage is required for each step.',
            'steps.*.max_percent.required' => 'Maximum percentage is required for each step.',
            'steps.*.max_percent.gte' => 'Maximum percentage must be greater than or equal to minimum.',
            'steps.*.grade.required' => 'Grade label is required for each step.',
        ];
    }
}
