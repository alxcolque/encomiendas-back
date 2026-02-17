<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'   => 'required|string|max:255',
            'phone'  => 'required|string|unique:users,phone',
            'pin'    => 'required|string|size:4',
            'city'   => 'required|string',
            'role'   => 'required|in:admin,driver',
            'status' => 'required|in:active,inactive,suspended',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'El nombre es obligatorio.',
            'phone.required'  => 'El teléfono es obligatorio.',
            'phone.unique'    => 'Ya existe un usuario con este número de teléfono.',
            'pin.required'    => 'El PIN es obligatorio para nuevos usuarios.',
            'pin.size'        => 'El PIN debe tener exactamente 4 dígitos.',
            'city.required'   => 'La ciudad es obligatoria.',
            'role.required'   => 'El rol es obligatorio.',
            'role.in'         => 'El rol seleccionado no es válido.',
            'status.required' => 'El estado es obligatorio.',
            'status.in'       => 'El estado seleccionado no es válido.',
        ];
    }
}