<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shipmentId = $this->route('shipment') ? $this->route('shipment')->id : null;

        $rules = [
            'origin_office_id' => 'nullable|exists:offices,id',
            'destination_office_id' => 'nullable|exists:offices,id',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'nullable|string|max:20',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'nullable|string|max:20',
            'current_status' => 'in:created,in_transit,at_office,out_for_delivery,delivered,cancelled',
            'estimated_delivery' => 'nullable|date',
            'price' => 'numeric|min:0',
        ];

        if ($this->isMethod('post')) {
            $rules['tracking_code'] = 'required|string|unique:shipments,tracking_code|max:50';
        } else {
            $rules['tracking_code'] = 'sometimes|string|unique:shipments,tracking_code,' . $shipmentId . '|max:50';
        }

        return $rules;
    }
}
