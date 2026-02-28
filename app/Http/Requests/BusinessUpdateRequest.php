<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $businessId = $this->route('business') ? $this->route('business')->id : null;

        return [
            'company_name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:activo,inactivo,bloqueado',
        ];
    }
}
