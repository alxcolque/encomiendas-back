<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $query = Shipment::with(['originOffice.city', 'destinationOffice.city', 'events', 'invoice']);

        if ($user) {
            if ($user->role === 'worker') {
                // Filter only by the offices directly assigned to this worker in office_user
                $officeIds = \Illuminate\Support\Facades\DB::table('office_user')
                    ->where('user_id', $user->id)
                    ->pluck('office_id')
                    ->toArray();

                if (empty($officeIds)) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where(function ($q) use ($officeIds) {
                        $q->whereIn('origin_office_id', $officeIds)
                            ->orWhereIn('destination_office_id', $officeIds);
                    });
                }
            } elseif ($user->role === 'company') {
                $query->where(function ($q) use ($user) {
                    $q->where('origin_office_id', $user->id)
                        ->orWhere('destination_office_id', $user->id);
                });
            }
        }

        return new \App\Http\Resources\Shipment\ShipmentCollection(
            $query->orderBy('created_at', 'desc')->paginate(20)
        );
    }

    public function store(\App\Http\Requests\ShipmentRequest $request)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Handle Sender
            if (!$request->filled('sender_id') && $request->filled('sender_ci')) {
                $sender = \App\Models\Client::firstOrCreate(
                    ['ci_nit' => $request->sender_ci],
                    [
                        'name'   => $request->sender_name,
                        'phone'  => $request->sender_phone,
                        'status' => 'normal',
                    ]
                );
                $data['sender_id'] = $sender->id;
            }

            // Handle Receiver
            if (!$request->filled('receiver_id') && $request->filled('receiver_name')) {
                $receiver = \App\Models\Client::firstOrCreate(
                    ['ci_nit' => $request->receiver_ci ?? '0'], // Use '0' or appropriate default if no CI is provided
                    [
                        'name'   => $request->receiver_name,
                        'phone'  => $request->receiver_phone,
                        'status' => 'normal',
                    ]
                );
                $data['receiver_id'] = $receiver->id;
            } else if (!$request->filled('receiver_id')) {
                $data['receiver_id'] = null;
            }

            // Check if user is client
            $user = $request->user();
            if ($user && get_class($user) === \App\Models\Client::class) {
                $data['from_client'] = true;
            } else {
                $data['from_client'] = false;
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
            // The invoice is now generated later via a separate endpoint explicitly called by the admin.

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
        $data = $request->validated();

        // Handle Receiver Update/Create
        if ($request->filled('receiver_name')) {
            if ($shipment->receiver_id) {
                $shipment->receiver->update([
                    'name'   => $request->receiver_name,
                    'ci_nit' => $request->receiver_ci ?? $shipment->receiver->ci_nit,
                    'phone'  => $request->receiver_phone ?? $shipment->receiver->phone,
                ]);
            } else {
                $receiver = \App\Models\Client::firstOrCreate(
                    ['ci_nit' => $request->receiver_ci ?? '0'],
                    [
                        'name'   => $request->receiver_name,
                        'phone'  => $request->receiver_phone,
                        'status' => 'normal',
                    ]
                );
                $data['receiver_id'] = $receiver->id;
            }
        }

        // Handle Sender Update
        if ($request->filled('sender_name') && $shipment->sender_id) {
            $shipment->sender->update([
                'name'   => $request->sender_name,
                'ci_nit' => $request->sender_ci ?? $shipment->sender->ci_nit,
                'phone'  => $request->sender_phone ?? $shipment->sender->phone,
            ]);
        }

        // Validate receiver before transition to in_transit
        if ($request->filled('current_status') && $request->current_status === 'in_transit') {
            $hasReceiver = isset($data['receiver_id']) || (!isset($data['receiver_id']) && $shipment->receiver_id);
            if (!$hasReceiver) {
                return response()->json(['message' => 'Debe asignar un destinatario para poder enviar (pasar a estado in_transit).'], 400);
            }
        }

        if ($request->filled('current_status') && $request->current_status === 'delivered') {
            if (!$shipment->invoice()->exists()) {
                $message = $shipment->tracking_pay == 2
                    ? 'Debe realizar el pago para entregar este envío (Pago en Destino).'
                    : 'No se puede marcar como entregado si no ha sido pagado.';
                return response()->json(['message' => $message], 400);
            }
        }

        // Clean up client fields from data so they aren't passed to the shipment model
        unset($data['sender_name'], $data['sender_ci'], $data['sender_phone']);
        unset($data['receiver_name'], $data['receiver_ci'], $data['receiver_phone']);
        
        // Ensure we don't accidentally overwrite sender_id or receiver_id with null
        if (array_key_exists('sender_id', $data) && is_null($data['sender_id'])) {
            unset($data['sender_id']);
        }
        if (array_key_exists('receiver_id', $data) && is_null($data['receiver_id'])) {
            unset($data['receiver_id']);
        }

        $shipment->update($data);
        return new \App\Http\Resources\Shipment\ShipmentResource($shipment->load(['originOffice.city', 'destinationOffice.city', 'events', 'invoice']));
    }

    public function destroy(Shipment $shipment)
    {
        $user = request()->user();
        if ($user && get_class($user) === \App\Models\Client::class) {
            if ($shipment->sender_id !== $user->id || $shipment->current_status !== 'quote') {
                return response()->json(['message' => 'No autorizado o el estado no permite eliminación.'], 403);
            }
        }
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

    public function scan(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string',
        ]);

        $shipment = Shipment::where('tracking_code', $request->tracking_code)->first();

        if (!$shipment) {
            return response()->json(['message' => 'Código de seguimiento no encontrado'], 404);
        }

        // Validación: Debe estar pagado (tiene factura) o por pagar (tracking_pay = 2)
        $isPaid = $shipment->invoice()->exists();
        $isToPay = $shipment->tracking_pay == 2;

        if (!$isPaid && !$isToPay) {
            return response()->json(['message' => 'La encomienda debe estar pagada o por pagar para cambiar su estado.'], 400);
        }

        // Validación: Solo estados específicos permiten cambio vía QR
        $allowedStates = ['created', 'in_transit', 'at_office'];
        if (!in_array($shipment->current_status, $allowedStates)) {
            return response()->json(['message' => 'El estado actual no permite cambios vía QR.'], 400);
        }

        $states = ['quote', 'created', 'in_transit', 'at_office', 'delivered'];
        $currentIndex = array_search($shipment->current_status, $states);

        if ($currentIndex === false) {
            return response()->json(['message' => 'Estado actual desconocido'], 400);
        }

        if ($currentIndex === count($states) - 1) {
            return response()->json(['message' => 'La encomienda ya se encuentra en el estado final (Entregado)'], 400);
        }

        $newStatus = $states[$currentIndex + 1];

        if ($newStatus === 'in_transit' && !$shipment->receiver_id) {
            return response()->json(['message' => 'Debe asignar un destinatario para poder enviar (pasar a estado in_transit).'], 400);
        }

        if ($newStatus === 'delivered' && !$shipment->invoice()->exists()) {
            $message = $shipment->tracking_pay == 2
                ? 'Debe realizar el pago en destino antes de marcar como entregado.'
                : 'No se puede entregar un envío que no ha sido pagado.';
            return response()->json(['message' => $message], 400);
        }

        $shipment->update([
            'current_status' => $newStatus
        ]);

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'new_status' => $newStatus
        ]);
    }

    public function generateInvoice(Request $request, Shipment $shipment)
    {
        $request->validate([
            'invoice_name' => 'nullable|string',
            'invoice_nit' => 'nullable|string',
            'business_name' => 'nullable|string',
            'nit_ci_emisor' => 'nullable|string',
        ]);

        if ($shipment->invoice) {
            return response()->json(['message' => 'Ya existe una factura para esta encomienda.'], 400);
        }

        if (!$shipment->receiver_id) {
            return response()->json(['message' => 'Debe asignar un destinatario para poder realizar el pago.'], 400);
        }

        $typeInv = "sin";
        if (Invoice::where('shipment_id', $shipment->id)) {
            $typeInv = "con";
        }

        $invoice = Invoice::create([
            'type'           => $typeInv,
            'shipment_id'    => $shipment->id,
            'business_name'  => $request->business_name ?? 'KOLMOX',
            'nit_ci_emisor'  => $request->nit_ci_emisor ?? '11192632',
            'receipt_name'   => $request->invoice_name ?? $shipment->sender_name ?? 'S/N',
            'doc_num'        => $request->invoice_nit ?? $shipment->sender_ci ?? '0',
            'details'        => [
                [
                    'description' => 'SERVICIO DE TRANSPORTE DE ENCOMIENDA: ' . $shipment->observation,
                    'qty'         => 1,
                    'unit'        => 1,
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

        // Ensure shipment with_invoice and status is updated
        /* $shipment->update([
            'with_invoice' => true,
            'current_status' => 'created'
        ]); */

        return response()->json([
            'message' => 'Invoice created successfully.',
            'invoice' => new \App\Http\Resources\Invoice\InvoiceResource($invoice)
        ], 201);
    }
}
