<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $driverId = $this->route('driver') ? $this->route('driver')->user_id : null;

        // For store: user_id is required and unique
        // For update: user_id is usually not updatable or already set
        $rules = [
            'vehicle_type' => 'nullable|string|max:50',
            'plate_number' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:50',
            'status' => 'in:active,inactive,on-delivery',
        ];

        if ($this->isMethod('post')) {
            $rules['user_id'] = 'required|exists:users,id|unique:drivers,user_id';
        }

        return $rules;
    }
}
