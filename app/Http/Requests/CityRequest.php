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
}
