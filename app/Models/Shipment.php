<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_code',
        'origin_office_id',
        'destination_office_id',
        'sender_name',
        'sender_phone',
        'receiver_name',
        'receiver_phone',
        'current_status',
        'estimated_delivery',
        'price',
    ];

    protected $casts = [
        'estimated_delivery' => 'datetime',
    ];

    public function originOffice()
    {
        return $this->belongsTo(Office::class, 'origin_office_id');
    }

    public function destinationOffice()
    {
        return $this->belongsTo(Office::class, 'destination_office_id');
    }

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
