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
        $driver = $this->route('driver');
        $userId = $driver ? $driver->user_id : null;

        $rules = [
            // Driver specific
            'vehicle_type' => 'required|string|max:50',
            'plate_number' => 'required|string|max:20',
            'license_number' => 'required|string|max:50',
            'status' => 'in:active,inactive,on-delivery',

            // User Selection
            'user_id' => $this->isMethod('post')
                ? 'required|exists:users,id|unique:drivers,user_id'
                : 'sometimes|exists:users,id|unique:drivers,user_id,' . $driver->id, // Usually user_id doesn't change on update, but if it does...

        ];

        return $rules;
    }
}
