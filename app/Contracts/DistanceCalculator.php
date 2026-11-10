<?php

namespace App\Contracts;

interface DistanceCalculator
{
    /** Distancia en kilómetros entre dos coordenadas. */
    public function kilometers(float $lat1, float $lng1, float $lat2, float $lng2): float;
}
