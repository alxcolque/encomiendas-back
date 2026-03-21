<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = is_object($user) ? $user->id : $user;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $userId,
            'pin' => 'nullable|string|size:4',
            'role' => 'in:admin,worker,driver,client,company,partner',
            'avatar' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe exceder los 255 caracteres.',
            'email.email' => 'El correo debe ser un correo válido.',
            'email.unique' => 'El correo ya existe.',
            'phone.max' => 'El teléfono no debe exceder los 20 caracteres.',
            'phone.unique' => 'El teléfono ya existe.',
            'pin.size' => 'El pin debe tener 4 dígitos.',
            'role.in' => 'El rol debe ser admin, worker, driver, client, company o partner.',
        ];
    }
}
