<?php

namespace App\Services\Geo;

use App\Contracts\DistanceCalculator;

/**
 * Distancia en kilómetros con la fórmula de Haversine.
 *
 * Se calcula en PHP en vez de SQL porque el motor usado en las pruebas
 * (SQLite) no trae funciones trigonométricas nativas.
 */
class HaversineDistance implements DistanceCalculator
{
    private const EARTH_RADIUS_KM = 6371;

    public function kilometers(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
