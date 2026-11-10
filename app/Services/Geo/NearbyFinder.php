<?php

namespace App\Services\Geo;

use App\Contracts\DistanceCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * HU3 / HU-04 / HU-05: registros de una consulta que caen dentro de un radio,
 * ordenados por cercanía. Sirve para cualquier modelo con columnas
 * latitude y longitude.
 *
 * Primero descarta en SQL todo lo que está fuera del cuadrado que rodea al
 * círculo (consulta barata, funciona en MySQL y SQLite) y después calcula la
 * distancia exacta solo para los que quedan.
 */
class NearbyFinder
{
    /** Kilómetros que mide un grado de latitud. */
    private const KM_PER_DEGREE = 111.32;

    public function __construct(private readonly DistanceCalculator $distance)
    {
    }

    /**
     * Cada resultado trae distance_km.
     *
     * @return Collection<int, Model>
     */
    public function within(Builder $query, float $lat, float $lng, float $radiusKm): Collection
    {
        $model = $query->getModel();
        $latDelta = $radiusKm / self::KM_PER_DEGREE;
        $lngDelta = $radiusKm / (self::KM_PER_DEGREE * max(cos(deg2rad($lat)), 0.01));

        return $query
            ->whereBetween($model->qualifyColumn('latitude'), [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween($model->qualifyColumn('longitude'), [$lng - $lngDelta, $lng + $lngDelta])
            ->get()
            ->filter(function (Model $item) use ($lat, $lng, $radiusKm) {
                $item->distance_km = $this->distance->kilometers(
                    $lat,
                    $lng,
                    (float) $item->latitude,
                    (float) $item->longitude
                );

                return $item->distance_km <= $radiusKm;
            })
            ->sortBy('distance_km')
            ->values();
    }
}
