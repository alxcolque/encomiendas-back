<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'siteName' => $this->site_name,
            'siteDescription' => $this->site_description,
            'keywords' => $this->keywords,
            'supportEmail' => $this->support_email,
            'supportPhone' => $this->support_phone,
            'address' => $this->address,
        ];
    }
}
