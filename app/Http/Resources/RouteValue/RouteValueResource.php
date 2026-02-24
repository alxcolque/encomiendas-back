<?php

namespace App\Http\Resources\RouteValue;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\City\CityResource;

class RouteValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'city_a'     => new CityResource($this->whenLoaded('cityA')),
            'city_b'     => new CityResource($this->whenLoaded('cityB')),
            'value'      => $this->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
