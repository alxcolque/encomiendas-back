<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index()
    {
        return new \App\Http\Resources\Shipment\ShipmentCollection(
            Shipment::with(['originOffice.city', 'destinationOffice.city', 'events', 'invoice'])->paginate(20)
        );
    }

    public function store(\App\Http\Requests\ShipmentRequest $request)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Handle Sender
            if (!$request->filled('sender_id')) {
                $sender = \App\Models\Client::create([
                    'name'   => $request->sender_name,
                    'ci_nit' => $request->sender_ci,
                    'phone'  => $request->sender_phone,
                    'status' => 'normal',
                ]);
                $data['sender_id'] = $sender->id;
            }

            // Handle Receiver
            if (!$request->filled('receiver_id')) {
                $receiver = \App\Models\Client::create([
                    'name'   => $request->receiver_name,
                    'ci_nit' => $request->receiver_ci,
                    'phone'  => $request->receiver_phone,
                    'status' => 'normal',
                ]);
                $data['receiver_id'] = $receiver->id;
            }

            // Calculate estimated delivery
            $typeService = $data['type_service'] ?? 'normal';
            $daysToAdd = match ($typeService) {
                'express'  => 2,
                'standard' => 5,
                default    => 8,
            };
            $data['estimated_delivery'] = now()->addDays($daysToAdd);

            $shipment = Shipment::create($data);

            // Handle Invoice
            if ($request->boolean('with_invoice')) {
                \App\Models\Invoice::create([
                    'type'           => 'con iva',
                    'shipment_id'    => $shipment->id,
                    'business_name'  => 'KOLMOX EXPRESS',
                    'nit_ci_emisor'  => '456489012',
                    'receipt_name'   => $request->invoice_name ?? $request->sender_name,
                    'doc_num'        => $request->invoice_nit ?? $request->sender_ci,
                    'details'        => [
                        [
                            'description' => 'SERVICIO DE TRANSPORTE DE ENCOMIENDA',
                            'qty'         => 1,
                            'unit'        => 58,
                            'unit_price'  => $shipment->price,
                            'discount'    => 0,
                            'sub_total'   => $shipment->price,
                        ]
                    ],
                    'payment_method' => 1, // 1 para efectivo
                    'total'          => $shipment->price,
                    'total_iva'      => $shipment->price,
                    'currency'       => 'BOB',
                    'status'         => 'paid',
                ]);
            }

            return new \App\Http\Resources\Shipment\ShipmentResource($shipment->load('invoice'));
        });
    }

    public function show(Shipment $shipment)
    {
        return new \App\Http\Resources\Shipment\ShipmentResource(
            $shipment->load(['originOffice.city', 'destinationOffice.city', 'events', 'invoice'])
        );
    }

    public function update(\App\Http\Requests\ShipmentRequest $request, Shipment $shipment)
    {
        $shipment->update($request->validated());
        return new \App\Http\Resources\Shipment\ShipmentResource($shipment);
    }

    public function destroy(Shipment $shipment)
    {
        $shipment->delete();
        return response()->noContent();
    }

    public function track($tracking_code)
    {
        $shipment = Shipment::where('tracking_code', $tracking_code)
            ->with(['originOffice.city', 'destinationOffice.city', 'events'])
            ->firstOrFail();

        return new \App\Http\Resources\Shipment\ShipmentResource($shipment);
    }
}
