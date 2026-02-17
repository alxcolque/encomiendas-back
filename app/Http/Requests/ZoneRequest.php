<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZoneRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'coordinates'  => 'required|array|min:3', // Un polígono necesita al menos 3 puntos
            'extra_rate'   => 'required|numeric|min:0',
            'color'        => 'nullable|string|max:7',
            'is_active'    => 'boolean'
        ];
    }
}