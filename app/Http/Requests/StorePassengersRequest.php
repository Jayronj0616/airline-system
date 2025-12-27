<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePassengersRequest extends FormRequest
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
            'contact_email' => 'required|email|max:255',
            'contact_password' => 'required|string|min:8|confirmed',
            'passengers' => 'required|array|min:1|max:9',
            'passengers.*.first_name' => 'required|string|max:100|regex:/^[\pL\s\-]+$/u',
            'passengers.*.last_name' => 'required|string|max:100|regex:/^[\pL\s\-]+$/u',
            'passengers.*.email' => 'required|email|max:150',
            'passengers.*.phone' => 'nullable|string|max:20|regex:/^[\d\s\-\+\(\)]+$/',
            'passengers.*.date_of_birth' => 'required|date|before:today|after:1900-01-01',
            'passengers.*.passport_number' => 'nullable|string|max:20|regex:/^[A-Z0-9]+$/i',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'contact_email.required' => 'Contact email is required.',
            'contact_email.email' => 'Please enter a valid email address.',
            'contact_password.required' => 'Password is required.',
            'contact_password.min' => 'Password must be at least 8 characters.',
            'contact_password.confirmed' => 'Password confirmation does not match.',
            'passengers.required' => 'Passenger information is required.',
            'passengers.*.first_name.required' => 'First name is required for all passengers.',
            'passengers.*.first_name.regex' => 'First name can only contain letters, spaces, and hyphens.',
            'passengers.*.last_name.required' => 'Last name is required for all passengers.',
            'passengers.*.last_name.regex' => 'Last name can only contain letters, spaces, and hyphens.',
            'passengers.*.email.required' => 'Email is required for all passengers.',
            'passengers.*.email.email' => 'Please enter a valid email address.',
            'passengers.*.phone.regex' => 'Phone number format is invalid.',
            'passengers.*.date_of_birth.required' => 'Date of birth is required for all passengers.',
            'passengers.*.date_of_birth.before' => 'Date of birth must be in the past.',
            'passengers.*.date_of_birth.after' => 'Date of birth must be after 1900.',
            'passengers.*.passport_number.regex' => 'Passport number can only contain letters and numbers.',
        ];
    }
}
