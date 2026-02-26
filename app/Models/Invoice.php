<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'FAC-' . strtoupper(bin2hex(random_bytes(4)));
            }
            if (empty($invoice->emit_date)) {
                $invoice->emit_date = now();
            }
        });
    }

    protected $fillable = [
        'type',
        'shipment_id',
        'business_name',
        'nit_ci_emisor',
        'invoice_number',
        'receipt_name',
        'doc_num',
        'complement',
        'cuf',
        'cufd',
        'cod_suc',
        'cod_sale',
        'emit_date',
        'details',
        'payment_method',
        'total',
        'total_iva',
        'currency',
        'status',
    ];

    protected $casts = [
        'emit_date' => 'datetime',
        'details' => 'array',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
