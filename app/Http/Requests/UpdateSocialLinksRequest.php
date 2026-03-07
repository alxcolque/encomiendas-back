<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialLinksRequest extends FormRequest
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
            'socials' => 'required|array',
            'socials.*.platform' => 'required|string|in:facebook,instagram,tiktok,whatsapp',
            'socials.*.url' => 'required|url',
            'socials.*.active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'socials.required' => 'La lista de redes sociales es requerida.',
            'socials.array' => 'Formato de redes sociales inválido.',
            'socials.*.platform.required' => 'La plataforma es requerida para una de las redes.',
            'socials.*.platform.in' => 'Una de las plataformas seleccionadas no es válida.',
            'socials.*.url.required' => 'La URL es requerida para una de las redes.',
            'socials.*.url.url' => 'Una de las URLs proporcionadas no es válida.',
        ];
    }
}
