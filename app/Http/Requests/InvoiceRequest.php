<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $invoiceId = $this->route('invoice') ? $this->route('invoice')->id : null;

        $rules = [
            'type' => 'required|string|max:50',
            'shipment_id' => 'required|exists:shipments,id',
            'business_name' => 'required|string|max:255',
            'nit_ci_emisor' => 'required|string|max:20',
            'receipt_name' => 'required|string|max:255',
            'doc_num' => 'required|string|max:20',
            'complement' => 'nullable|string|max:5',
            'cuf' => 'nullable|string|max:255',
            'cufd' => 'nullable|string|max:255',
            'cod_suc' => 'nullable|integer',
            'cod_sale' => 'nullable|integer',
            'emit_date' => 'nullable|date',
            'details' => 'required|array',
            'payment_method' => 'nullable|integer',
            'total' => 'required|numeric|min:0',
            'total_iva' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
        ];

        if ($this->isMethod('post')) {
            $rules['invoice_number'] = 'nullable|string|unique:invoices,invoice_number|max:50';
        } else {
            $rules['invoice_number'] = 'nullable|string|unique:invoices,invoice_number,' . $invoiceId . '|max:50';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de factura es obligatorio.',
            'shipment_id.required' => 'El envío es obligatorio.',
            'shipment_id.exists' => 'El envío seleccionado no existe.',
            'business_name.required' => 'La razón social es obligatoria.',
            'nit_ci_emisor.required' => 'El NIT/CI del emisor es obligatorio.',
            'receipt_name.required' => 'El nombre del recibo es obligatorio.',
            'doc_num.required' => 'El número de documento es obligatorio.',
            'details.required' => 'Los detalles de la factura son obligatorios.',
            'details.array' => 'Los detalles deben tener formato válido.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un número.',
            'total.min' => 'El total no puede ser negativo.',
            'invoice_number.unique' => 'El número de factura ya está en uso.',
        ];
    }
}
