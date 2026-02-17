<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        return \App\Http\Resources\InvoiceResource::collection(Invoice::with('shipment')->paginate(20));
    }

    public function store(\App\Http\Requests\InvoiceRequest $request)
    {
        $invoice = Invoice::create($request->validated());
        return new \App\Http\Resources\InvoiceResource($invoice);
    }

    public function show(Invoice $invoice)
    {
        return new \App\Http\Resources\InvoiceResource($invoice->load('shipment'));
    }

    public function update(\App\Http\Requests\InvoiceRequest $request, Invoice $invoice)
    {
        $invoice->update($request->validated());
        return new \App\Http\Resources\InvoiceResource($invoice);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->noContent();
    }
}
