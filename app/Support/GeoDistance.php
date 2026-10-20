<?php

namespace App\Support;

class GeoDistance
{
    /**
     * Distancia en kilómetros entre dos coordenadas (fórmula de Haversine).
     *
     * Se calcula en PHP en vez de SQL porque el motor de base de datos usado
     * en desarrollo (SQLite) no trae funciones trigonométricas nativas.
     */
    public static function kilometers(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
