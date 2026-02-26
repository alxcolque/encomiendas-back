<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    // Note: We are using a simple model test without DB if possible, 
    // but JSON casting usually needs interaction with the model's attributes.

    public function test_invoice_model_casts_details_to_array()
    {
        $invoice = new Invoice();
        $details = [
            'description' => 'Servicio de transporte',
            'qty' => 1,
            'unit' => 58,
            'unit_price' => 100,
            'discount' => 0,
            'sub_total' => 100
        ];

        $invoice->details = $details;

        $this->assertIsArray($invoice->details);
        $this->assertEquals('Servicio de transporte', $invoice->details['description']);
    }

    public function test_invoice_has_correct_fillable_fields()
    {
        $invoice = new Invoice();
        $fillable = $invoice->getFillable();

        $this->assertContains('type', $fillable);
        $this->assertContains('nit_ci_emisor', $fillable);
        $this->assertContains('details', $fillable);
        $this->assertContains('total', $fillable);
        $this->assertContains('emit_date', $fillable);
    }
}
