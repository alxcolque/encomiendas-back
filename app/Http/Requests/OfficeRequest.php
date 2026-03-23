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
            'users.*' => [
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $officeId = request()->route('office') ? request()->route('office')->id : null;
                    
                    $query = \Illuminate\Support\Facades\DB::table('office_user')->where('user_id', $value);
                    if ($officeId) {
                        $query->where('office_id', '!=', $officeId);
                    }
                    
                    if ($query->exists()) {
                        $fail('El usuario ya es encargado de otra oficina.');
                    }
                }
            ],
            'status' => 'in:active,inactive',
            'coordinates' => 'nullable|string|max:100',
            'image' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la oficina es obligatorio.',
            'city_id.required' => 'La ciudad es obligatoria.',
            'city_id.exists' => 'La ciudad seleccionada no existe.',
            'address.required' => 'La dirección es obligatoria.',
            'users.*.exists' => 'Uno de los usuarios seleccionados no existe.',
            'status.in' => 'El estado seleccionado es inválido.',
        ];
    }
}
