<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($shipment) {
            if (empty($shipment->tracking_code)) {
                $shipment->tracking_code = 'SH-' . strtoupper(bin2hex(random_bytes(4)));
            }
        });
    }

    protected $fillable = [
        'tracking_code',
        'origin_office_id',
        'destination_office_id',
        'sender_id',
        'receiver_id',
        'tracking_pay',
        'is_pack',
        'is_fragile',
        'type_service',
        'track_type',
        'observation',
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

    public function sender()
    {
        return $this->belongsTo(Client::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Client::class, 'receiver_id');
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
