<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'ci_nit',
        'phone',
        'status',
        'observations',
    ];

    // Relación inversa: un cliente puede tener muchos envíos
    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'sender_id');
    }
}
