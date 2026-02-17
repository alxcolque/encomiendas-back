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
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $userId,
            'password' => 'nullable|string|min:6',
            'role' => 'in:admin,worker,driver,client',
            'avatar' => 'nullable|string',
        ];
    }
}
