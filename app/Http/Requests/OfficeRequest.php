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
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string|max:255',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'status' => 'in:active,inactive',
            'coordinates' => 'nullable|string|max:100',
        ];
    }
}
