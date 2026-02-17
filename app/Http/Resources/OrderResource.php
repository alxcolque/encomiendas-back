<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string)$this->id,
            'type'            => $this->type,
            'fee'             => (float)$this->delivery_fee, // Mapeo de delivery_fee a fee
            'points'          => (int)$this->points,
            'bonusPoints'     => 0, // Campo para lógica futura o bonus
            'distance'        => $this->duration, // O cálculo de distancia si lo tienes
            'zone'            => $this->address, // Mapeo de address a zone o detalle
            'pickupAddress'   => $this->pickup,
            'deliveryAddress' => $this->address,
            'customerName'    => $this->client_name,
            'urgency'         => $this->mapUrgency($this->urgency), // 'low' | 'medium' | 'high'
            'expiresAt'       => $this->created_at->addMinutes(30),
            'status'          => $this->status,
            'assignedTo'      => (string)$this->user_id,
            'createdAt'       => $this->created_at,
            'acceptedAt'      => $this->updated_at,
            'completedAt'     => null,
        ];
    }

    private function mapUrgency($urgency)
    {
        $map = ['baja' => 'low', 'media' => 'medium', 'alta' => 'high'];
        return $map[$urgency] ?? 'medium';
    }
}