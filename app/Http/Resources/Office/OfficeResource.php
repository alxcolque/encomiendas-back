<?php

namespace App\Http\Resources\Office;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\City\CityResource;

class OfficeResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'city_id'     => $this->city_id,
            'city'        => new CityResource($this->whenLoaded('city')),
            'address'     => $this->address,
            'status'      => $this->status,
            'coordinates' => $this->coordinates,
            'image'       => $this->image,
            'managers'    => $this->whenLoaded(
                'managers',
                fn() =>
                $this->managers->map(fn($m) => ['id' => (string) $m->id, 'name' => $m->name, 'phone' => $m->phone])
            ),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
