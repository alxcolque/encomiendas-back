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
}
