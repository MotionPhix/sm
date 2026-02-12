<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'class_stream_assignment_id' => ['required', 'integer', 'exists:class_stream_assignments,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a teacher.',
            'class_stream_assignment_id.required' => 'Please select a classroom.',
            'subject_id.required' => 'Please select a subject.',
        ];
    }
}
