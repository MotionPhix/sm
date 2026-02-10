<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $school = $this->user()->activeSchool;
        $classId = $this->route('schoolClass')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('school_classes')
                    ->where('school_id', $school->id)
                    ->ignore($classId),
            ],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('school_classes')
                    ->where('school_id', $school->id)
                    ->ignore($classId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Class name is required.',
            'name.unique' => 'A class with this name already exists.',
            'order.required' => 'Class order is required.',
            'order.unique' => 'This order number is already taken.',
            'order.min' => 'Order must be at least 1.',
        ];
    }
}
