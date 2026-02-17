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
        // Obtenemos el ID del usuario desde la ruta
        $userId = $this->route('user')->id;

        return [
            'name'   => 'required|string|max:255',
            'phone'  => 'required|string|unique:users,phone,' . $userId,
            'pin'    => 'nullable|string|size:4', // Opcional al editar
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
            'phone.unique'    => 'Este teléfono ya está siendo usado por otro usuario.',
            'pin.size'        => 'Si vas a cambiar el PIN, debe tener 4 dígitos.',
            'city.required'   => 'La ciudad es obligatoria.',
            'role.required'   => 'El rol es obligatorio.',
            'status.required' => 'El estado es obligatorio.',
        ];
    }
}