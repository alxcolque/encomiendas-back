<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessFactory> */
    use HasFactory;

    protected $fillable = [
        'company_name',
        'code',
        'phone',
        'description',
        'location',
        'status',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($business) {
            $nextId = (static::max('id') ?? 0) + 1;
            $business->code = 'KOLMOX-EN-VER-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        });
    }
}
