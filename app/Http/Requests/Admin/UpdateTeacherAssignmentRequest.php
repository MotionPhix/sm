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
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'stream_id' => ['required', 'integer', 'exists:streams,id'],
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
            'school_class_id.required' => 'Please select a class.',
            'stream_id.required' => 'Please select a stream.',
            'subject_id.required' => 'Please select a subject.',
        ];
    }
}
