<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStreamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $school = $this->user()->activeSchool;
        $streamId = $this->route('stream')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('streams')
                    ->where('school_id', $school->id)
                    ->ignore($streamId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Stream name is required.',
            'name.unique' => 'A stream with this name already exists.',
        ];
    }
}
