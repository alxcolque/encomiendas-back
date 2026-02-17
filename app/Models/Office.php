<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'address',
        'phone',
        'manager',
        'status',
        'coordinates',
    ];

    public function shipmentsSent()
    {
        return $this->hasMany(Shipment::class, 'origin_office_id');
    }

    public function shipmentsReceived()
    {
        return $this->hasMany(Shipment::class, 'destination_office_id');
    }
}
