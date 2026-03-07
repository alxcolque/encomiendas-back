<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $driver = $this->route('driver');
        $userId = $driver ? $driver->user_id : null;

        $rules = [
            // Driver specific
            'vehicle_type' => 'required|string|max:50',
            'plate_number' => 'required|string|max:20',
            'license_number' => 'required|string|max:50',
            'status' => 'in:active,inactive,on-delivery',

            // User Selection
            'user_id' => $this->isMethod('post')
                ? 'required|exists:users,id|unique:drivers,user_id'
                : 'sometimes|exists:users,id|unique:drivers,user_id,' . $driver->id, // Usually user_id doesn't change on update, but if it does...

        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'vehicle_type.required' => 'El tipo de vehículo es obligatorio.',
            'vehicle_type.max' => 'El tipo de vehículo no debe exceder los 50 caracteres.',
            'plate_number.required' => 'El número de placa es obligatorio.',
            'plate_number.max' => 'El número de placa no debe exceder los 20 caracteres.',
            'license_number.required' => 'El número de licencia es obligatorio.',
            'license_number.max' => 'El número de licencia no debe exceder los 50 caracteres.',
            'status.in' => 'El estado seleccionado es inválido.',
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'user_id.unique' => 'Este usuario ya está asignado como conductor.',
        ];
    }
}
