<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLegalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terms_and_conditions' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'terms_and_conditions.string' => 'Los términos y condiciones deben ser texto.',
            'privacy_policy.string' => 'La política de privacidad debe ser texto.',
        ];
    }
}
