<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'active',
        'order_index', // Mapped to 'order' in migration? No, migration has order_index from previous migration. 
        // Wait, I didn't change faqs table migration, I only viewed it. It has order_index.
        // But user asked for 'order'. I should probably rename it in a new migration or just map it.
        // Let's check the migration file again. It says 'order_index'.
        // User asked for 'order'. 
        // I will use 'order_index' in DB but expose it as 'order' in API resource to satisfy requirement without extra migration if possible, 
        // or better yet, I should have updated the migration. But I already ran migrate. 
        // Simplest is to map it in Resource.
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
