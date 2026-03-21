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
        // Fallback for older orders without estimated_delivery
        $estimatedDelivery = $this->estimated_delivery;
        if (!$estimatedDelivery && $this->created_at) {
            $daysToAdd = match ($this->type_service) {
                'express'  => 2,
                'standard' => 5,
                default    => 8,
            };
            $estimatedDelivery = $this->created_at->copy()->addDays($daysToAdd);
        }

        return [
            'id' => $this->id,
            'tracking_code' => $this->tracking_code,
            'origin_office_id' => $this->origin_office_id,
            'destination_office_id' => $this->destination_office_id,
            'sender_name' => $this->sender->name ?? null,
            'sender_ci' => $this->sender->ci_nit ?? null,
            'sender_phone' => $this->sender->phone ?? null,
            'receiver_name' => $this->receiver->name ?? null,
            'receiver_ci' => $this->receiver->ci_nit ?? null,
            'receiver_phone' => $this->receiver->phone ?? null,
            'current_status' => $this->current_status,
            'estimated_delivery' => $estimatedDelivery,
            'price' => $this->price,
            'discount' => $this->discount,
            'weight' => $this->weight,
            'width' => $this->width,
            'length' => $this->length,
            'height' => $this->height,
            'is_pack' => $this->is_pack,
            'is_fragile' => $this->is_fragile,
            'with_invoice' => $this->with_invoice,
            'type_service' => $this->type_service,
            'track_type' => $this->track_type,
            'tracking_pay' => $this->tracking_pay,
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
