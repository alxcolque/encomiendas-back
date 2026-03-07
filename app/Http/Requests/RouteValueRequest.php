<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouteValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'city_a' => ($isUpdate ? 'sometimes|' : '') . 'required|exists:cities,id',
            'city_b' => ($isUpdate ? 'sometimes|' : '') . 'required|exists:cities,id|different:city_a',
            'value'  => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'city_a.required' => 'La ciudad de origen es obligatoria.',
            'city_a.exists' => 'La ciudad de origen seleccionada no existe.',
            'city_b.required' => 'La ciudad de destino es obligatoria.',
            'city_b.exists' => 'La ciudad de destino seleccionada no existe.',
            'city_b.different' => 'Las ciudades de origen y destino no pueden ser iguales.',
            'value.required' => 'El costo es obligatorio.',
            'value.numeric' => 'El costo debe ser un número.',
            'value.min' => 'El costo no puede ser negativo.',
        ];
    }
}
