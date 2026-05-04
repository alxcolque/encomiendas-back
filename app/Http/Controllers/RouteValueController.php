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
        // 1. Obtener ciudades con ubicación
        $cities = City::whereNotNull('location')->get();
        $factor = 0.47 / 217;
        $now = now();

        // 2. Obtener rutas existentes (clave direccional: A-B)
        $existingRoutes = RouteValue::all(['city_a', 'city_b'])
            ->mapWithKeys(function ($rv) {
                $key = $rv->city_a . '-' . $rv->city_b;
                return [$key => true];
            });

        $newRoutes = [];
        $cityList = $cities->values();
        $count = $cityList->count();

        // 3. Comparar todas las ciudades con todas (Direccional A->B)
        for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j < $count; $j++) {
                if ($i === $j) continue; // No permitir rutas entre la misma ciudad

                $cityA = $cityList[$i];
                $cityB = $cityList[$j];

                // Validar formato de ubicación
                $locA = array_map('trim', explode(',', $cityA->location));
                $locB = array_map('trim', explode(',', $cityB->location));

                if (count($locA) !== 2 || count($locB) !== 2) {
                    continue;
                }

                // Generar clave direccional para búsqueda rápida
                $key = $cityA->id . '-' . $cityB->id;

                // Si no existe, se crea
                if (!$existingRoutes->has($key)) {
                    $distance = $this->calculateDistance(
                        (float)$locA[0], (float)$locA[1],
                        (float)$locB[0], (float)$locB[1]
                    );

                    $value = round($distance * $factor, 2);

                    $newRoutes[] = [
                        'city_a'     => $cityA->id,
                        'city_b'     => $cityB->id,
                        'value'      => $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $existingRoutes->put($key, true);
                }
            }
        }

        // 4. Inserción masiva en bloques
        if (!empty($newRoutes)) {
            foreach (array_chunk($newRoutes, 100) as $chunk) {
                RouteValue::insert($chunk);
            }
        }

        return response()->json([
            'message' => 'Rutas generadas correctamente',
            'created' => count($newRoutes)
        ]);
    }
}
