<?php

namespace App\Http\Resources\Invoice;

use App\Http\Resources\Shipment\ShipmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'shipment_id' => $this->shipment_id,
            'business_name' => $this->business_name,
            'nit_ci_emisor' => $this->nit_ci_emisor,
            'invoice_number' => $this->invoice_number,
            'receipt_name' => $this->receipt_name,
            'doc_num' => $this->doc_num,
            'complement' => $this->complement,
            'cuf' => $this->cuf,
            'cufd' => $this->cufd,
            'cod_suc' => $this->cod_suc,
            'cod_sale' => $this->cod_sale,
            'emit_date' => $this->emit_date,
            'details' => $this->details,
            'payment_method' => $this->payment_method,
            'total' => $this->total,
            'total_iva' => $this->total_iva,
            'currency' => $this->currency,
            'status' => $this->status,
            'shipment' => new ShipmentResource($this->whenLoaded('shipment')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
