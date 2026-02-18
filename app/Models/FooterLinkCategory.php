<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterLinkCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function links()
    {
        return $this->hasMany(FooterLink::class);
    }
}
