<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeatsRequest extends FormRequest
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
            'seats' => 'required|json',
            'seats.*' => 'integer|exists:seats,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'seats.required' => 'Please select your seats.',
            'seats.json' => 'Invalid seat selection format.',
            'seats.*.integer' => 'Invalid seat ID.',
            'seats.*.exists' => 'One or more selected seats do not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('seats') && is_string($this->seats)) {
            $this->merge([
                'seats' => json_decode($this->seats, true) ?? [],
            ]);
        }
    }
}
