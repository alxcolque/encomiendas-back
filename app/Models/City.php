<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
    ];

    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    public function routesAsA()
    {
        return $this->hasMany(RouteValue::class, 'city_a');
    }

    public function routesAsB()
    {
        return $this->hasMany(RouteValue::class, 'city_b');
    }
}
