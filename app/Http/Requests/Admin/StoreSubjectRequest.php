<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $school = $this->user()->activeSchool;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('subjects')->where('school_id', $school->id),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('subjects')->where('school_id', $school->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Subject name is required.',
            'name.unique' => 'A subject with this name already exists.',
            'code.required' => 'Subject code is required.',
            'code.unique' => 'A subject with this code already exists.',
        ];
    }
}
