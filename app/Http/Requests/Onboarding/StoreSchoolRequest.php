<?php

namespace App\Http\Requests\Onboarding;

use App\Enums\SchoolType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(SchoolType::class)],
            'country' => ['required', 'string', 'max:100'],
            'region' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'phone:INTERNATIONAL,!mobile'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'School name is required',
            'name.max' => 'School name cannot exceed 255 characters',
            'type.required' => 'Please select a school type',
            'type.enum' => 'Invalid school type selected',
            'country.required' => 'Country is required',
            'region.required' => 'Region is required',
            'district.required' => 'District is required',
            'email.email' => 'Please enter a valid email address',
            'phone.phone' => 'Phone number must be a valid international ground phone',
        ];
    }
}
