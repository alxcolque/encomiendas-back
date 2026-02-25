<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Http\Resources\Office\OfficeCollection;
use App\Http\Resources\Office\OfficeResource;
use App\Http\Requests\OfficeRequest;

class OfficeController extends Controller
{
    public function index()
    {
        return new OfficeCollection(Office::with('city', 'managers')->get());
    }

    public function store(OfficeRequest $request)
    {
        $office = Office::create($request->validated());
        if ($request->has('users')) {
            $office->managers()->sync($request->input('users'));
        }
        $office->load('city', 'managers');
        return new OfficeResource($office);
    }

    public function show(Office $office)
    {
        $office->load('city', 'managers');
        return new OfficeResource($office);
    }

    public function update(OfficeRequest $request, Office $office)
    {
        $office->update($request->validated());
        if ($request->has('users')) {
            $office->managers()->sync($request->input('users'));
        }
        $office->load('city', 'managers');
        return new OfficeResource($office);
    }

    public function destroy(Office $office)
    {
        $office->delete();
        return response()->noContent();
    }
}
