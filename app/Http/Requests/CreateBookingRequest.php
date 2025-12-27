<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
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
            'flight_id' => 'required|integer|exists:flights,id',
            'fare_class_id' => 'required|integer|exists:fare_classes,id',
            'seat_count' => 'required|integer|min:1|max:9',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'flight_id.required' => 'Please select a flight.',
            'flight_id.exists' => 'Selected flight does not exist.',
            'fare_class_id.required' => 'Please select a fare class.',
            'fare_class_id.exists' => 'Selected fare class does not exist.',
            'seat_count.required' => 'Please specify number of seats.',
            'seat_count.min' => 'You must book at least 1 seat.',
            'seat_count.max' => 'You can only book up to 9 seats per booking.',
        ];
    }
}
