<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessStoreRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:activo,inactivo,bloqueado',
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'El nombre de la empresa es obligatorio.',
            'company_name.max' => 'El nombre de la empresa no debe exceder los 255 caracteres.',
            'phone.max' => 'El teléfono no debe exceder los 20 caracteres.',
            'location.max' => 'La ubicación no debe exceder los 255 caracteres.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado es inválido.',
        ];
    }
}
