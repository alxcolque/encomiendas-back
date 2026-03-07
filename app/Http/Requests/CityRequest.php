<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'name'     => ($isUpdate ? 'sometimes|' : '') . 'required|string|max:150',
            'location' => 'nullable|string|max:250',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la ciudad es obligatorio.',
            'name.string' => 'El nombre de la ciudad debe ser texto.',
            'name.max' => 'El nombre de la ciudad no debe exceder los 150 caracteres.',
            'location.max' => 'La ubicación no debe exceder los 250 caracteres.',
        ];
    }
}
