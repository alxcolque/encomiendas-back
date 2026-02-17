<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index()
    {
        return new \App\Http\Resources\Office\OfficeCollection(Office::all());
    }

    public function store(\App\Http\Requests\OfficeRequest $request)
    {
        $office = Office::create($request->validated());
        return new \App\Http\Resources\Office\OfficeResource($office);
    }

    public function show(Office $office)
    {
        return new \App\Http\Resources\Office\OfficeResource($office);
    }

    public function update(\App\Http\Requests\OfficeRequest $request, Office $office)
    {
        $office->update($request->validated());
        return new \App\Http\Resources\Office\OfficeResource($office);
    }

    public function destroy(Office $office)
    {
        $office->delete();
        return response()->noContent();
    }
}
