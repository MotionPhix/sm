<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.create');
    }

    public function rules(): array
    {
        $school = $this->user()->activeSchool;

        return [
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
                function ($attribute, $value, $fail) use ($school) {
                    $exists = $school->academicYears()->where('id', $value)->exists();
                    if (!$exists) {
                        $fail('The selected academic year is invalid.');
                    }
                },
            ],
            'grouping_strategy' => ['required', 'in:individual,primary-secondary'],
            'school_class_ids' => ['required', 'array', 'min:1'],
            'school_class_ids.*' => [
                function ($attribute, $value, $fail) use ($school) {
                    // Allow 'primary' and 'secondary' as special values for grouping
                    if (in_array($value, ['primary', 'secondary'])) {
                        return;
                    }

                    // Otherwise validate as class ID
                    $exists = $school->classes()->where('id', $value)->exists();
                    if (!$exists) {
                        $fail('One or more selected classes are invalid.');
                    }
                },
            ],
            'term_id' => ['nullable', 'exists:terms,id'],
            'fee_items' => ['required', 'array', 'min:1'],
            'fee_items.*.fee_item_id' => ['required', 'exists:fee_items,id'],
            'fee_items.*.amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'fee_items.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'fee_items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Please select an academic year.',
            'academic_year_id.exists' => 'The selected academic year is invalid.',
            'grouping_strategy.required' => 'Please select how to group classes.',
            'grouping_strategy.in' => 'The selected grouping strategy is invalid.',
            'school_class_ids.required' => 'Please select at least one class or grouping.',
            'school_class_ids.min' => 'Please select at least one class or grouping.',
            'school_class_ids.*.custom' => 'One or more selected classes are invalid.',
            'fee_items.required' => 'You must assign at least one fee item.',
            'fee_items.min' => 'You must assign at least one fee item.',
            'fee_items.*.fee_item_id.required' => 'Fee item is required for each row.',
            'fee_items.*.fee_item_id.exists' => 'One or more selected fee items are invalid.',
            'fee_items.*.amount.required' => 'Amount is required for each fee item.',
            'fee_items.*.amount.numeric' => 'Amount must be a valid number.',
            'fee_items.*.amount.min' => 'Amount must be at least 0.',
        ];
    }
}
