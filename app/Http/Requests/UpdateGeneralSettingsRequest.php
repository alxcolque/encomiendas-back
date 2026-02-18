<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
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
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'keywords' => 'nullable|string',
            'support_email' => 'required|email|max:255',
            'support_phone' => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
        ];
    }
}
