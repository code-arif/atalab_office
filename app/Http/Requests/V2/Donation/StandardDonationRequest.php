<?php

namespace App\Http\Requests\V2\Donation;

use Illuminate\Foundation\Http\FormRequest;

class StandardDonationRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'payment_method_type' => 'nullable|in:card,us_bank_account',
            'is_cover' => 'nullable|boolean',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'A user ID is required to create a donation.',
            'user_id.exists'   => 'The specified user does not exist.',
        ];
    }
}
