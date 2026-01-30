<?php

namespace App\Http\Requests\Registrar;

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('students.transfer');
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
            'transfer_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'class_stream_assignment_id.required' => 'Please select a target class/stream.',
            'class_stream_assignment_id.exists' => 'The selected class/stream is not valid.',
            'transfer_date.required' => 'Please select a transfer date.',
            'reason.required' => 'Please provide a reason for the transfer.',
        ];
    }
}
