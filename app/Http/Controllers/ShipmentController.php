<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index()
    {
        return new \App\Http\Resources\Shipment\ShipmentCollection(
            Shipment::with(['originOffice', 'destinationOffice', 'events'])->paginate(20)
        );
    }

    public function store(\App\Http\Requests\ShipmentRequest $request)
    {
        $shipment = Shipment::create($request->validated());
        return new \App\Http\Resources\Shipment\ShipmentResource($shipment);
    }

    public function show(Shipment $shipment)
    {
        return new \App\Http\Resources\Shipment\ShipmentResource(
            $shipment->load(['originOffice', 'destinationOffice', 'events', 'invoice'])
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
            ->with(['originOffice', 'destinationOffice', 'events'])
            ->firstOrFail();

        return new \App\Http\Resources\Shipment\ShipmentResource($shipment);
    }
}
