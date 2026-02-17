<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['name', 'coordinates', 'extra_rate', 'color', 'is_active'];

    protected $casts = [
        'coordinates' => 'array', // Crucial para que GeoService funcione
        'extra_rate'  => 'float',
    ];
}
