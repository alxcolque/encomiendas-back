<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfficeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            // 'phone' => 'nullable|string|max:20', // Removed
            // 'manager' => 'nullable|string|max:255', // Removed
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'status' => 'in:active,inactive',
            'coordinates' => 'nullable|string|max:100',
        ];
    }
}
