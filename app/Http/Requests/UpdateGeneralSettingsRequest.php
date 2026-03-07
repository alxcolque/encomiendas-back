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

    public function messages(): array
    {
        return [
            'site_name.required' => 'El nombre del sitio es obligatorio.',
            'site_name.max' => 'El nombre del sitio no debe exceder los 255 caracteres.',
            'support_email.required' => 'El correo de soporte es obligatorio.',
            'support_email.email' => 'El correo de soporte debe ser válido.',
            'support_email.max' => 'El correo de soporte no debe exceder los 255 caracteres.',
            'support_phone.required' => 'El teléfono de soporte es obligatorio.',
            'support_phone.max' => 'El teléfono de soporte no debe exceder los 50 caracteres.',
        ];
    }
}
