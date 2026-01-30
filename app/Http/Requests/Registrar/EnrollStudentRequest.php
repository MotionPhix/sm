<?php

namespace App\Http\Requests\Registrar;

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('students.enroll');
    }

    public function rules(): array
    {
        $schoolId = $this->user()->activeSchool?->id;

        $currentYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        return [
            'class_stream_assignment_id' => [
                'required',
                'integer',
                Rule::exists('class_stream_assignments', 'id')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
                ),
            ],
            'enrollment_date' => ['required', 'date'],
            'academic_year_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_years', 'id')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->where('is_current', true)
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'class_stream_assignment_id.required' => 'Please select a class/stream.',
            'class_stream_assignment_id.exists' => 'The selected class/stream is not valid.',
            'enrollment_date.required' => 'Please select an enrollment date.',
        ];
    }
}
