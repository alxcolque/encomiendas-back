<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        return \App\Http\Resources\DriverResource::collection(Driver::with('user')->get());
    }

    public function store(\App\Http\Requests\DriverRequest $request)
    {
        $driver = Driver::create($request->validated());
        return new \App\Http\Resources\DriverResource($driver);
    }

    public function show(Driver $driver)
    {
        return new \App\Http\Resources\DriverResource($driver->load('user'));
    }

    public function update(\App\Http\Requests\DriverRequest $request, Driver $driver)
    {
        $driver->update($request->validated());
        return new \App\Http\Resources\DriverResource($driver->load('user'));
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();
        return response()->noContent();
    }
}
