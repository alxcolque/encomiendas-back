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
            'sender_id' => 'nullable|exists:clients,id',
            'receiver_id' => 'nullable|exists:clients,id',
            // New client data if IDs are not provided
            'sender_name' => 'required_without:sender_id|string',
            'sender_ci' => 'required_without:sender_id|string',
            'sender_phone' => 'required_without:sender_id|string',
            'receiver_name' => 'required_without:receiver_id|string',
            'receiver_ci' => 'required_without:receiver_id|string',
            'receiver_phone' => 'required_without:receiver_id|string',

            'tracking_pay' => 'nullable|integer|in:1,2,3',
            'is_pack' => 'nullable|boolean',
            'type_service' => 'nullable|in:normal,standard,express',
            'track_type' => 'nullable|integer|in:1,2',
            'observation' => 'nullable|string',
            'current_status' => 'in:created,in_transit,at_office,out_for_delivery,delivered,cancelled',
            'estimated_delivery' => 'nullable|date',
            'price' => 'numeric|min:0',

            // Optional Invoice Data
            'with_invoice' => 'nullable|boolean',
            'invoice_nit' => 'required_if:with_invoice,true|string|max:20',
            'invoice_name' => 'required_if:with_invoice,true|string|max:255',
        ];

        if ($this->isMethod('post')) {
            $rules['tracking_code'] = 'nullable|string|unique:shipments,tracking_code|max:50';
        } else {
            $rules['tracking_code'] = 'sometimes|nullable|string|unique:shipments,tracking_code,' . $shipmentId . '|max:50';
        }

        return $rules;
    }
}
