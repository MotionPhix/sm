<?php

namespace App\Http\Requests\Admin;

use App\Models\FeeItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeeItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.update');
    }

    public function rules(): array
    {
        $feeItem = $this->route('feeItem');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fee_items')->where(function ($query) {
                    return $query->where('school_id', $this->user()->activeSchool->id);
                })->ignore($feeItem->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'code' => [
                'required',
                'string',
                'max:10',
                'uppercase',
                Rule::unique('fee_items')->ignore($feeItem->id),
            ],
            'category' => [
                'required',
                'string',
                Rule::in(array_keys(FeeItem::categories())),
            ],
            'is_mandatory' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The fee item name is required.',
            'name.unique' => 'A fee item with this name already exists in your school.',
            'code.required' => 'The fee code is required.',
            'code.uppercase' => 'The fee code must be uppercase.',
            'category.required' => 'Please select a fee category.',
        ];
    }
}
