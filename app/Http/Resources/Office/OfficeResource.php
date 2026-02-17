<?php

namespace App\Http\Resources\Office;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'address' => $this->address,
            'phone' => $this->phone,
            'hours' => 'Lun - Vie: 08:30 - 18:30 | Sáb: 09:00 - 13:00', // Static for now
            'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=800', // Placeholder
            'manager' => $this->manager,
            'status' => $this->status,
            'coordinates' => $this->coordinates,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
