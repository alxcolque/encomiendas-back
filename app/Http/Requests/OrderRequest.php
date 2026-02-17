<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Permitir si el usuario está autenticado vía middleware
    }

    public function rules(): array
    {
        return [
            'type'         => 'required|in:estandar,express,programada',
            'client_name'  => 'required|string|max:255',
            'description'  => 'nullable|string',
            'pickup'       => 'required|string',
            'delivery'     => 'required|string',
            'address'      => 'required|string',
            'delivery_fee' => 'required|numeric|min:0',
            'urgency'      => 'required|in:baja,media,alta',
            'currency'     => 'nullable|string|max:10',
            'status'       => 'nullable|string',
            'duration'     => 'required|string',
            'points'       => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'El tipo de pedido no es válido.',
            'pickup.required' => 'La ubicación de partida es obligatoria.',
            'delivery.required' => 'La ubicación de entrega es obligatoria.',
        ];
    }
}
