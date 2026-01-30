<?php

namespace App\Http\Requests\Registrar;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('students.edit');
    }

    public function rules(): array
    {
        $schoolId = $this->user()->activeSchool?->id;
        $studentId = $this->route('student')?->id;

        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('students')->where(fn ($q) => $q->where('school_id', $schoolId))->ignore($studentId),
            ],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'string', Rule::in(['male', 'female'])],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'admission_date' => ['required', 'date'],
            'national_id' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name is required.',
            'first_name.unique' => 'A student with this name already exists in your school.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ];
    }
}
