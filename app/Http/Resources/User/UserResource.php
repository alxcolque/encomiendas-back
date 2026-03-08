<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Driver\DriverResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'driver_profile' => new DriverResource($this->whenLoaded('driverProfile')),
            'offices' => $this->whenLoaded('offices', function () {
                return $this->offices->map(function ($office) {
                    return [
                        'id' => $office->id,
                        'name' => $office->name,
                    ];
                });
            }),
        ];
    }
}
