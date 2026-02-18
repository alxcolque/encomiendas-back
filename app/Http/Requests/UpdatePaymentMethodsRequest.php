<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodsRequest extends FormRequest
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
            'paymentMethods' => 'required|array',
            'paymentMethods.*.label' => 'required|string',
            'paymentMethods.*.icon' => 'required|string',
            'paymentMethods.*.active' => 'boolean',
        ];
    }
}
