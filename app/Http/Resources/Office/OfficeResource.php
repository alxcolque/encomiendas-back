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
            'status' => $this->status,
            'coordinates' => $this->coordinates,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
