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
            'shipment_id' => 'required|exists:shipments,id',
            'nit_ci' => 'required|string|max:20',
            'business_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'status' => 'in:paid,pending,cancelled',
        ];

        if ($this->isMethod('post')) {
            $rules['invoice_number'] = 'nullable|string|unique:invoices,invoice_number|max:50';
        } else {
            $rules['invoice_number'] = 'nullable|string|unique:invoices,invoice_number,' . $invoiceId . '|max:50';
        }

        return $rules;
    }
}
