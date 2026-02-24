<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_a',
        'city_b',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function cityA()
    {
        return $this->belongsTo(City::class, 'city_a');
    }

    public function cityB()
    {
        return $this->belongsTo(City::class, 'city_b');
    }
}
