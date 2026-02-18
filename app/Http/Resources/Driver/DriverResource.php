<?php

namespace App\Http\Resources\Driver;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->whenLoaded('user');
        $isUserLoaded = !($user instanceof \Illuminate\Http\Resources\MissingValue);

        return [
            'id' => $this->user_id,
            'user_id' => $this->user_id,
            'name' => $isUserLoaded ? $user->name : '',
            'email' => $isUserLoaded ? $user->email : '',
            'phone' => $isUserLoaded ? $user->phone : '',
            'avatar' => $isUserLoaded ? $user->avatar : '',
            'vehicle_type' => $this->vehicle_type,
            'plate_number' => $this->plate_number,
            'license_number' => $this->license_number,
            'rating' => $this->rating,
            'total_deliveries' => $this->total_deliveries,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
