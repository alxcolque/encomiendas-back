<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'type', // ej: 'estandar', 'express', 'programada'
        'client_name',
        'pickup',            // Dirección de recogida
        'delivery',          // Dirección de entrega
        'address', // Detalles extra
        'delivery_fee',
        'description',
        'urgency', // ['baja', 'media', 'alta'],
        'currency',
        'status',
        'duration',
        'points',
    ];
    protected $casts = [
        'delivery_fee' => 'float',
        'points' => 'integer'
    ];

    public function assignments() { return $this->hasMany(OrderAssignment::class); }
    // Para saber quién tiene la orden ahora
    public function currentDriver() { return $this->hasOneThrough(User::class, OrderAssignment::class, 'order_id', 'id', 'id', 'user_id')->where('order_assignments.status', 'accepted'); }
}
