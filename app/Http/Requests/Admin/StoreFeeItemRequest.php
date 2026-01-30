<?php

namespace App\Http\Requests\Admin;

use App\Models\FeeItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeeItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('fees.create');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fee_items')->where(function ($query) {
                    return $query->where('school_id', $this->user()->activeSchool->id);
                }),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'code' => [
                'required',
                'string',
                'max:10',
                'uppercase',
                Rule::unique('fee_items'),
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
            'code.unique' => 'This fee code is already in use. Please use a unique code.',
            'code.uppercase' => 'The fee code must be uppercase.',
            'category.required' => 'Please select a fee category.',
            'category.in' => 'The selected category is invalid.',
        ];
    }
}
