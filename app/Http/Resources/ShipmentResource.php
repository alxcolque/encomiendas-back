<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tracking_code' => $this->tracking_code,
            'origin_office_id' => $this->origin_office_id,
            'destination_office_id' => $this->destination_office_id,
            'sender_name' => $this->sender_name,
            'sender_phone' => $this->sender_phone,
            'receiver_name' => $this->receiver_name,
            'receiver_phone' => $this->receiver_phone,
            'current_status' => $this->current_status,
            'estimated_delivery' => $this->estimated_delivery,
            'price' => $this->price,
            'origin_office' => new OfficeResource($this->whenLoaded('originOffice')),
            'destination_office' => new OfficeResource($this->whenLoaded('destinationOffice')),
            'events' => ShipmentEventResource::collection($this->whenLoaded('events')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
