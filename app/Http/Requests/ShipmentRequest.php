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
            'receiver_name' => 'nullable|string',
            'receiver_ci' => 'nullable|string',
            'receiver_phone' => 'nullable|string',

            'tracking_pay' => 'nullable|integer|in:1,2,3',
            'is_pack' => 'nullable|boolean',
            'type_service' => 'nullable|in:normal,standard,express',
            'track_type' => 'nullable|integer|in:1,2',
            'observation' => 'nullable|string',
            'current_status' => 'in:quote,created,in_transit,at_office,delivered',
            'estimated_delivery' => 'nullable|date',
            'price' => 'numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',

            // Optional Invoice Data
            'with_invoice' => 'nullable|boolean',
            'invoice_nit' => 'required_if:with_invoice,true|string|max:20',
            'invoice_name' => 'required_if:with_invoice,true|string|max:255',
        ];

        if ($this->isMethod('patch')) {
            $rules = collect($rules)->map(function ($rule) {
                return 'sometimes|' . $rule;
            })->toArray();
        }

        if ($this->isMethod('post')) {
            $rules['tracking_code'] = 'nullable|string|unique:shipments,tracking_code|max:50';
        } else {
            $rules['tracking_code'] = 'sometimes|nullable|string|unique:shipments,tracking_code,' . $shipmentId . '|max:50';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'origin_office_id.required' => 'La oficina de origen es requerida.',
            'origin_office_id.exists' => 'La oficina de origen no existe.',
            'destination_office_id.required' => 'La oficina de destino es requerida.',
            'destination_office_id.exists' => 'La oficina de destino no existe.',
            'sender_id.exists' => 'El remitente seleccionado no existe.',
            'receiver_id.exists' => 'El destinatario seleccionado no existe.',
            'sender_name.required_without' => 'El nombre del remitente es requerido cuando no se selecciona un remitente existente.',
            'sender_ci.required_without' => 'El CI del remitente es requerido.',
            'sender_phone.required_without' => 'El teléfono del remitente es requerido.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio no puede ser negativo.',
            'invoice_nit.required_if' => 'El NIT es requerido si se solicita factura.',
            'invoice_name.required_if' => 'El nombre para la factura es requerido.',
            'tracking_code.unique' => 'El código de seguimiento ya está en uso.',
        ];
    }
}
