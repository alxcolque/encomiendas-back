<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Http\Requests\CityRequest;
use App\Http\Resources\City\CityCollection;
use App\Http\Resources\City\CityResource;

class CityController extends Controller
{
    public function index()
    {
        return new CityCollection(City::all());
    }

    public function store(CityRequest $request)
    {
        $city = City::create($request->validated());
        return new CityResource($city);
    }

    public function show(City $city)
    {
        return new CityResource($city);
    }

    public function update(CityRequest $request, City $city)
    {
        $city->update($request->validated());
        return new CityResource($city);
    }

    public function destroy(City $city)
    {
        $city->delete();
        return response()->noContent();
    }
}
