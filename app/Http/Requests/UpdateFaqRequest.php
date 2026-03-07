<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'faqs' => 'required|array',
            'faqs.*.id' => 'nullable',
            'faqs.*.question' => 'required|string',
            'faqs.*.answer' => 'required|string',
            'faqs.*.active' => 'boolean',
            'faqs.*.order' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'faqs.required' => 'La lista de preguntas es requerida.',
            'faqs.array' => 'Formato de preguntas inválido.',
            'faqs.*.question.required' => 'Una de las preguntas está vacía.',
            'faqs.*.answer.required' => 'Una de las respuestas está vacía.',
        ];
    }
}
