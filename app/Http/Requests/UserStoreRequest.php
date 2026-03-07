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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'pin' => 'required|string|size:4',
            'role' => 'in:admin,worker,driver,client',
            'avatar' => 'nullable|string',
        ];
    }
    /* Messages in spanish */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'email.required' => 'El correo es requerido',
            'email.email' => 'El correo debe ser un correo valido',
            'email.unique' => 'El correo ya existe',
            'phone.required' => 'El telefono es requerido',
            'phone.unique' => 'El telefono ya existe',
            'pin.required' => 'El pin es requerido',
            'pin.size' => 'El pin debe tener 4 digitos',
            'role.required' => 'El rol es requerido',
            'role.in' => 'El rol debe ser admin, worker, driver o client',
            'avatar.required' => 'La imagen es requerida',
        ];
    }
}
