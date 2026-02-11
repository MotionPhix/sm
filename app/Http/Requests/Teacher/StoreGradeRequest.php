<?php

namespace App\Http\Requests\Teacher;

use App\Models\AssessmentPlan;
use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('exams.enter-marks');
    }

    public function rules(): array
    {
        $school = $this->user()->activeSchool;

        return [
            'assessment_plan_id' => [
                'required',
                'integer',
                'exists:assessment_plans,id',
            ],
            'class_stream_assignment_id' => [
                'required',
                'integer',
                'exists:class_stream_assignments,id',
            ],
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'grades.*.score' => ['nullable', 'numeric', 'min:0'],
            'grades.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Additional validation after standard rules pass.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $assessmentPlan = AssessmentPlan::find($this->assessment_plan_id);

            if (! $assessmentPlan) {
                return;
            }

            // Validate each score does not exceed max_score
            foreach ($this->grades as $index => $gradeData) {
                if (isset($gradeData['score']) && $gradeData['score'] !== null) {
                    if ($gradeData['score'] > $assessmentPlan->max_score) {
                        $validator->errors()->add(
                            "grades.{$index}.score",
                            "Score cannot exceed the maximum of {$assessmentPlan->max_score}."
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'assessment_plan_id.required' => 'Please select an assessment.',
            'assessment_plan_id.exists' => 'The selected assessment is invalid.',
            'class_stream_assignment_id.required' => 'Please select a class.',
            'class_stream_assignment_id.exists' => 'The selected class is invalid.',
            'grades.required' => 'Grade data is required.',
            'grades.min' => 'At least one grade entry is required.',
            'grades.*.student_id.required' => 'Student ID is required.',
            'grades.*.student_id.exists' => 'Invalid student.',
            'grades.*.score.numeric' => 'Score must be a number.',
            'grades.*.score.min' => 'Score cannot be negative.',
        ];
    }
}
