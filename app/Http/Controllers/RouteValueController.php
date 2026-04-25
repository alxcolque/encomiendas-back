<?php

namespace App\Http\Controllers;

use App\Models\RouteValue;
use App\Models\City;
use App\Http\Requests\RouteValueRequest;
use App\Http\Resources\RouteValue\RouteValueCollection;
use App\Http\Resources\RouteValue\RouteValueResource;

use Illuminate\Http\Request;

class RouteValueController extends Controller
{
    public function index()
    {
        return new RouteValueCollection(RouteValue::with('cityA', 'cityB')->get());
    }

    public function findByCities(Request $request)
    {
        $cityA = $request->query('city_a');
        $cityB = $request->query('city_b');

        $routeValue = RouteValue::where('city_a', $cityA)
            ->where('city_b', $cityB)
            ->first();

        if (!$routeValue) {
            return response()->json(['data' => null]);
        }

        return new RouteValueResource($routeValue);
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

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Result in km
    }

    public function generate()
    {
        $cities = City::all();
        $factor = 0.47 / 217;
        $createdCount = 0;

        foreach ($cities as $cityA) {
            foreach ($cities as $cityB) {
                // Skip if same city
                if ($cityA->id === $cityB->id) {
                    continue;
                }

                // Check if both have location
                if (!$cityA->location || !$cityB->location) {
                    continue;
                }

                $locA = array_map('trim', explode(',', $cityA->location));
                $locB = array_map('trim', explode(',', $cityB->location));

                if (count($locA) !== 2 || count($locB) !== 2) {
                    continue;
                }

                $lat1 = (float)$locA[0];
                $lon1 = (float)$locA[1];
                $lat2 = (float)$locB[0];
                $lon2 = (float)$locB[1];

                $distance = $this->calculateDistance($lat1, $lon1, $lat2, $lon2);
                $value = round($distance * $factor, 2);

                // Insert if not exists
                $exists = RouteValue::where('city_a', $cityA->id)
                    ->where('city_b', $cityB->id)
                    ->exists();

                if (!$exists) {
                    RouteValue::create([
                        'city_a' => $cityA->id,
                        'city_b' => $cityB->id,
                        'value' => $value
                    ]);
                    $createdCount++;
                }
            }
        }

        return response()->json([
            'message' => 'Rutas generadas correctamente',
            'created' => $createdCount
        ]);
    }
}
