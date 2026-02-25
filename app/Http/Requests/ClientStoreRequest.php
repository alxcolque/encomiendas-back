<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'ci_nit'       => 'required|string|max:50|unique:clients,ci_nit',
            'phone'        => 'nullable|string|max:20',
            'status'       => 'sometimes|in:normal,blocked,deleted',
            'observations' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'El nombre es obligatorio.',
            'ci_nit.required'  => 'El CI/NIT es obligatorio.',
            'ci_nit.unique'    => 'El CI/NIT ya está registrado.',
        ];
    }
}
