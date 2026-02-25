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
            'city_b.different' => 'Las ciudades de origen y destino no pueden ser iguales.',
        ];
    }
}
