<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => 'required|string|max:50',
            'url' => 'required|string|max:255',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'platform.required' => 'La plataforma es requerida.',
            'platform.max' => 'La plataforma no debe exceder los 50 caracteres.',
            'url.required' => 'La URL es requerida.',
            'url.max' => 'La URL no debe exceder los 255 caracteres.',
        ];
    }
}
