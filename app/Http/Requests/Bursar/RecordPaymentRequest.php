<?php

namespace App\Http\Requests\Bursar;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('payments.create');
    }

    public function rules(): array
    {
        // Get the invoice to validate payment amount against
        $invoice = $this->route('invoice');
        $maxAmount = $invoice ? ($invoice->total_amount - $invoice->paid_amount) : null;

        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                $maxAmount ? "max:{$maxAmount}" : '',
            ],
            'payment_method' => [
                'required',
                'string',
                Rule::in(['cash', 'bank_transfer', 'mobile_money', 'cheque', 'card', 'other']),
            ],
            'payment_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        $invoice = $this->route('invoice');
        $formattedMaxAmount = $invoice ? 'MK '.number_format($invoice->total_amount - $invoice->paid_amount, 2) : '';

        return [
            'amount.required' => 'Please enter the payment amount.',
            'amount.min' => 'Payment amount must be greater than zero.',
            'amount.max' => "Payment amount cannot exceed the outstanding balance of {$formattedMaxAmount}.",
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'The selected payment method is not valid.',
            'payment_date.required' => 'Please enter the payment date.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
        ];
    }
}
