<?php

namespace App\Http\Resources\Shipment;

use App\Http\Resources\Invoice\InvoiceResource;
use App\Http\Resources\Office\OfficeResource;
use App\Http\Resources\ShipmentEvent\ShipmentEventResource;
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
            'sender_name' => $this->sender->name ?? null,
            'sender_phone' => $this->sender->phone ?? null,
            'receiver_name' => $this->receiver->name ?? null,
            'receiver_phone' => $this->receiver->phone ?? null,
            'current_status' => $this->current_status,
            'estimated_delivery' => $this->estimated_delivery,
            'price' => $this->price,
            'weight' => $this->weight,
            'is_pack' => $this->is_pack,
            'observation' => $this->observation,
            'origin_office' => new OfficeResource($this->whenLoaded('originOffice')),
            'destination_office' => new OfficeResource($this->whenLoaded('destinationOffice')),
            'events' => ShipmentEventResource::collection($this->whenLoaded('events', function () {
                return $this->events->sortByDesc('timestamp');
            })),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
