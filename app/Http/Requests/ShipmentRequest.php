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
            'origin_office_id' => 'required|exists:offices,id',
            'destination_office_id' => 'required|exists:offices,id',
            'sender_id' => 'required|exists:clients,id',
            'receiver_id' => 'required|exists:clients,id',
            'tracking_pay' => 'nullable|integer|in:1,2,3',
            'is_pack' => 'nullable|boolean',
            'type_service' => 'nullable|in:normal,standard,express',
            'track_type' => 'nullable|integer|in:1,2',
            'observation' => 'nullable|string',
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
