<?php

namespace App\Http\Requests\Registrar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('students.create');
    }

    public function rules(): array
    {
        $schoolId = $this->user()->activeSchool?->id;

        return [
            'applicant_id' => [
                'nullable',
                'integer',
                Rule::exists('applicants', 'id')->where(function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId)
                        ->where('status', 'admitted');
                }),
            ],
            'first_name' => [
                'required_without:applicant_id',
                'nullable',
                'string',
                'max:100',
                Rule::unique('students')->where(fn ($q) => $q->where('school_id', $schoolId)),
            ],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required_without:applicant_id', 'nullable', 'string', 'max:100'],
            'gender' => ['required_without:applicant_id', 'nullable', 'string', Rule::in(['male', 'female'])],
            'date_of_birth' => ['required_without:applicant_id', 'nullable', 'date', 'before:today'],
            'admission_date' => ['required_without:applicant_id', 'nullable', 'date'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'enroll_immediately' => ['sometimes', 'boolean'],
            'class_stream_assignment_id' => [
                'required_if:enroll_immediately,true',
                'nullable',
                'integer',
                Rule::exists('class_stream_assignments', 'id')->where(fn ($q) => $q->where('school_id', $schoolId)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'applicant_id.exists' => 'The selected applicant is not valid or has already been enrolled.',
            'first_name.required' => 'The first name is required.',
            'first_name.unique' => 'A student with this name already exists in your school.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'class_stream_assignment_id.required_if' => 'Please select a class/stream to enroll the student.',
            'class_stream_assignment_id.exists' => 'The selected class/stream is not valid.',
        ];
    }
}
