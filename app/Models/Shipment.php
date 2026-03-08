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
                /* KOL-(dos letras del origen)-(dos letras del destino)-(numeros incrementales) */
                $origin_office = Office::find($shipment->origin_office_id);
                $destination_office = Office::find($shipment->destination_office_id);
                $shipment->tracking_code = 'KOL-' . strtoupper(substr($origin_office->city->name, 0, 2)) . '-' . strtoupper(substr($destination_office->city->name, 0, 2)) . '-' . str_pad(Shipment::count() + 1, 4, '0', STR_PAD_LEFT);
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
        'weight',
        'with_invoice',
        'from_client',
        'discount',
    ];

    protected $casts = [
        'estimated_delivery' => 'datetime',
        'with_invoice' => 'boolean',
        'from_client' => 'boolean',
        'discount' => 'decimal:2',
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
