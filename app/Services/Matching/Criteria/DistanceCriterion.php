<?php

namespace App\Services\Matching\Criteria;

use App\Contracts\MatchCriterion;
use App\Models\PetReport;
use App\Services\Matching\CriterionResult;

/** Todo el peso en el mismo punto, nada en el borde del radio. */
class DistanceCriterion implements MatchCriterion
{
    public function __construct(private readonly float $radiusKm)
    {
    }

    public function evaluate(PetReport $report, PetReport $candidate): CriterionResult
    {
        $distance = (float) $candidate->distance_km;

        return CriterionResult::partial(
            1 - $distance / $this->radiusKm,
            'A '.number_format($distance, 1).' km',
        );
    }
}
