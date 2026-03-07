<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Client extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'ci_nit',
        'phone',
        'status',
        'observations',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['role'];

    /**
     * Get the client's role.
     *
     * @return string
     */
    public function getRoleAttribute()
    {
        return 'client';
    }

    // Relación inversa: un cliente puede tener muchos envíos
    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'sender_id');
    }
}
