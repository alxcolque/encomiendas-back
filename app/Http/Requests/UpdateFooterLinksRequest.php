<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFooterLinksRequest extends FormRequest
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
            'footerLinks' => 'required|array',
            // Structure: footerLinks: { services: [ {name, href} ], ... }
            // Validation for dynamic keys is tricky, but we can validate the values
            'footerLinks.*' => 'array',
            'footerLinks.*.*.name' => 'required|string',
            'footerLinks.*.*.href' => 'required|string',
        ];
    }
}
