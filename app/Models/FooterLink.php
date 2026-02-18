<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'footer_link_category_id',
        'name',
        'href',
        'order',
    ];

    public function category()
    {
        return $this->belongsTo(FooterLinkCategory::class, 'footer_link_category_id');
    }
}
