<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string',
            'answer' => 'required|string',
            'active' => 'boolean',
            'order_index' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'La pregunta es obligatoria.',
            'question.string' => 'La pregunta debe ser texto.',
            'answer.required' => 'La respuesta es obligatoria.',
            'answer.string' => 'La respuesta debe ser texto.',
            'active.boolean' => 'El estado activo debe ser verdadero o falso.',
            'order_index.integer' => 'El índice de orden debe ser un número entero.',
        ];
    }
}
