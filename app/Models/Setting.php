<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'site_description',
        'keywords',
        'support_email',
        'support_phone',
        'address',
        'terms_and_conditions',
        'privacy_policy',
    ];
}
