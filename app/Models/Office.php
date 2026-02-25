<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city_id',
        'address',
        'status',
        'coordinates',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function managers()
    {
        return $this->belongsToMany(User::class, 'office_user');
    }

    public function shipmentsSent()
    {
        return $this->hasMany(Shipment::class, 'origin_office_id');
    }

    public function shipmentsReceived()
    {
        return $this->hasMany(Shipment::class, 'destination_office_id');
    }
}
