<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:credit_card,debit_card,paypal',
            'cardholder_name' => 'required_if:payment_method,credit_card,debit_card|string|max:255|regex:/^[\pL\s\-\.]+$/u',
            'card_number' => 'required_if:payment_method,credit_card,debit_card|string|size:16|regex:/^[0-9]+$/',
            'expiry_month' => 'required_if:payment_method,credit_card,debit_card|integer|between:1,12',
            'expiry_year' => 'required_if:payment_method,credit_card,debit_card|integer|min:' . date('Y'),
            'cvv' => 'required_if:payment_method,credit_card,debit_card|string|size:3|regex:/^[0-9]+$/',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'cardholder_name.required_if' => 'Cardholder name is required.',
            'cardholder_name.regex' => 'Cardholder name can only contain letters, spaces, hyphens, and periods.',
            'card_number.required_if' => 'Card number is required.',
            'card_number.size' => 'Card number must be exactly 16 digits.',
            'card_number.regex' => 'Card number must contain only digits.',
            'expiry_month.required_if' => 'Expiry month is required.',
            'expiry_month.between' => 'Expiry month must be between 1 and 12.',
            'expiry_year.required_if' => 'Expiry year is required.',
            'expiry_year.min' => 'Card has expired.',
            'cvv.required_if' => 'CVV is required.',
            'cvv.size' => 'CVV must be exactly 3 digits.',
            'cvv.regex' => 'CVV must contain only digits.',
        ];
    }
}
