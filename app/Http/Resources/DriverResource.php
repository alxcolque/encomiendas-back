<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'vehicle_type' => $this->vehicle_type,
            'plate_number' => $this->plate_number,
            'license_number' => $this->license_number,
            'rating' => $this->rating,
            'total_deliveries' => $this->total_deliveries,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
