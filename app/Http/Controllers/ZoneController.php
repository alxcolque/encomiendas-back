<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Http\Requests\ZoneRequest;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZoneController extends Controller
{
    protected $geoService;

    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * Endpoint para consultar recargo por coordenadas.
     */
    public function checkRate(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $extraRate = $this->geoService->calculateExtraRate(
            $request->lat, 
            $request->lng
        );

        return response()->json([
            'extra_rate' => $extraRate,
            'is_special_zone' => $extraRate > 0
        ]);
    }
    /**
     * Lista todas las zonas (para el panel administrativo)
     */
    public function index()
    {
        return response()->json(Zone::all());
    }

    /**
     * Guarda una nueva zona con su polígono
     */
    public function store(ZoneRequest $request)
    {
        $zone = Zone::create($request->validated());

        return response()->json([
            'message' => 'Zona creada con éxito',
            'zone' => $zone
        ], 201);
    }

    /**
     * Muestra el detalle de una zona específica
     */
    public function show(Zone $zone)
    {
        return response()->json($zone);
    }

    /**
     * Actualiza los límites o el costo de una zona
     */
    public function update(ZoneRequest $request, Zone $zone)
    {
        $zone->update($request->validated());

        return response()->json([
            'message' => 'Zona actualizada correctamente',
            'zone' => $zone
        ]);
    }

    /**
     * Elimina una zona
     */
    public function destroy(Zone $zone)
    {
        $zone->delete();
        return response()->json(['message' => 'Zona eliminada']);
    }

    /**
     * Método extra: Obtener solo zonas activas para el mapa del cliente/admin
     */
    public function activeZones()
    {
        return response()->json(Zone::where('is_active', true)->get());
    }
    /**
     * Expande una URL corta de Google Maps para obtener la URL larga con coordenadas.
     */
    public function expandShortUrl(Request $request)
{
    $request->validate(['url' => 'required|url']);

    try {
        // Realizamos una petición GET sin seguir redirecciones automáticas
        $response = Http::withoutRedirecting()
            ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->get($request->url);

        // El destino real está en la cabecera 'Location'
        $url = $response->header('Location') ?? $request->url;
        // 1. Extraer la parte después de "/search/"
        $path = parse_url($url, PHP_URL_PATH); // "/maps/search/-17.995549,+-67.062355"
        $parts = explode('/', trim($path, '/'));

        // La parte de coordenadas normalmente estará al final
        $coordsPart = end($parts); // "-17.995549,+-67.062355"

        // 2. Limpiar y separar lat y lon
        $coordsPart = str_replace(' ', '', $coordsPart); // por si hay espacios
        $coords = explode(',', $coordsPart);

        // Validar que haya 2 partes
        $longUrl = $coords[0] . ',' . $coords[1];

        return response()->json([
            'success' => true,
            'longUrl' => $longUrl
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error al expandir'], 422);
    }
}
}