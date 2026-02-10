<?php

namespace App\Http\Requests\Admin;

use App\Enums\SchoolType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = $this->user()->activeSchool->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('schools')->ignore($schoolId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
            ],
            'type' => [
                'required',
                Rule::enum(SchoolType::class),
            ],
            'district' => [
                'nullable',
                'string',
                'max:100',
            ],
            'country' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'School name is required.',
            'email.required' => 'School email is required.',
            'email.email' => 'Please provide a valid email address.',
            'phone.required' => 'School phone number is required.',
            'type.required' => 'School type is required.',
            'code.unique' => 'This school code is already in use.',
        ];
    }
}
