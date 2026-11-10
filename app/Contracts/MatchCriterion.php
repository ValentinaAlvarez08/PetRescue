<?php

namespace App\Contracts;

use App\Models\PetReport;
use App\Services\Matching\CriterionResult;

/**
 * HU-05: un criterio del puntaje de parecido (distancia, colores, raza...).
 * Devuelve qué fracción de su peso suma (0 a 1), una razón legible opcional,
 * o descarta la pareja por completo.
 */
interface MatchCriterion
{
    public function evaluate(PetReport $report, PetReport $candidate): CriterionResult;
}
