<?php

namespace App\Http\Controllers;

use App\Models\RouteValue;
use App\Http\Requests\RouteValueRequest;
use App\Http\Resources\RouteValue\RouteValueCollection;
use App\Http\Resources\RouteValue\RouteValueResource;

class RouteValueController extends Controller
{
    public function index()
    {
        return new RouteValueCollection(RouteValue::with('cityA', 'cityB')->get());
    }

    public function store(RouteValueRequest $request)
    {
        $routeValue = RouteValue::create($request->validated());
        $routeValue->load('cityA', 'cityB');
        return new RouteValueResource($routeValue);
    }

    public function show(RouteValue $routeValue)
    {
        $routeValue->load('cityA', 'cityB');
        return new RouteValueResource($routeValue);
    }

    public function update(RouteValueRequest $request, RouteValue $routeValue)
    {
        $routeValue->update($request->validated());
        $routeValue->load('cityA', 'cityB');
        return new RouteValueResource($routeValue);
    }

    public function destroy(RouteValue $routeValue)
    {
        $routeValue->delete();
        return response()->noContent();
    }
}
