<?php

// app/Services/GeoService.php
namespace App\Services;

use App\Models\Zone;

class GeoService
{
    /**
     * Verifica si un punto está dentro de un polígono (Algoritmo Ray-casting)
     */
    public function isPointInPolygon($lat, $lng, $polygon)
    {
        if (!is_array($polygon) || count($polygon) < 3) {
            return false;
        }

        $inside = false;
        $points = array_values($polygon); // Asegura índices numéricos
        $count = count($points);
        $j = $count - 1;

        for ($i = 0; $i < $count; $i++) {
            $xi = $points[$i][0]; $yi = $points[$i][1];
            $xj = $points[$j][0]; $yj = $points[$j][1];

            // Verificación de intersección con variables PHP ($)
            if (($yi > $lng) != ($yj > $lng)) {
                $intersect = ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);
                
                if ($intersect) {
                    $inside = !$inside;
                }
            }
            $j = $i;
        }

        return $inside;
    }

    /**
     * Busca en la BD y retorna el recargo total.
     */
    public function calculateExtraRate($lat, $lng)
    {
        $zones = Zone::where('is_active', true)->get();
        $totalExtra = 0;

        foreach ($zones as $zone) {
            if ($this->isPointInPolygon($lat, $lng, $zone->coordinates)) {
                $totalExtra += $zone->extra_rate;
            }
        }
        return $totalExtra;
    }
}
