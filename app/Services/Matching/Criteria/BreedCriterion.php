<?php

namespace App\Services\Matching\Criteria;

use App\Contracts\MatchCriterion;
use App\Models\PetReport;
use App\Services\Matching\CriterionResult;

/** Raza (quien encuentra una mascota muchas veces no la sabe). */
class BreedCriterion implements MatchCriterion
{
    public function evaluate(PetReport $report, PetReport $candidate): CriterionResult
    {
        if (! $report->breed || ! $candidate->breed) {
            return CriterionResult::unknown();
        }

        return mb_strtolower($report->breed) === mb_strtolower($candidate->breed)
            ? CriterionResult::full("Misma raza: {$candidate->breed}")
            : CriterionResult::none();
    }
}
