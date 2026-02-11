<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssessmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('exams.create');
    }

    public function rules(): array
    {
        $school = $this->user()->activeSchool;

        return [
            'term_id' => [
                'required',
                'integer',
                Rule::exists('terms', 'id')->where('school_id', $school->id),
            ],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where('school_id', $school->id),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('assessment_plans')
                    ->where('term_id', $this->term_id)
                    ->where('subject_id', $this->subject_id),
            ],
            'ordering' => ['required', 'integer', 'min:1', 'max:20'],
            'max_score' => ['required', 'integer', 'min:1', 'max:999'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'term_id.required' => 'Please select a term.',
            'term_id.exists' => 'The selected term is invalid.',
            'subject_id.required' => 'Please select a subject.',
            'subject_id.exists' => 'The selected subject is invalid.',
            'name.required' => 'Assessment name is required.',
            'name.unique' => 'An assessment with this name already exists for the selected term and subject.',
            'max_score.required' => 'Maximum score is required.',
            'max_score.min' => 'Maximum score must be at least 1.',
            'weight.required' => 'Weight percentage is required.',
            'weight.max' => 'Weight cannot exceed 100%.',
        ];
    }
}
