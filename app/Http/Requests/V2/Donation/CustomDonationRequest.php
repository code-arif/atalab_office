<?php

namespace App\Http\Requests\V2\Donation;

use Illuminate\Foundation\Http\FormRequest;

class CustomDonationRequest extends FormRequest
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
            'user_id'             => 'required|integer|exists:users,id',
            'amount'              => 'required|numeric|min:26',
            'payment_method_type' => 'nullable|in:card,us_bank_account',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'A user ID is required.',
            'amount.required'  => 'A donation amount is required.',
            'amount.min'       => 'Custom donations must be at least $26.',
        ];
    }
}
