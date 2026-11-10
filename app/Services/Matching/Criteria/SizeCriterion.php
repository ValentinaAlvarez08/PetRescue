<?php

namespace App\Services\Matching\Criteria;

use App\Contracts\MatchCriterion;
use App\Models\PetReport;
use App\Services\Matching\CriterionResult;

/** Tamaño (solo los perros lo tienen). */
class SizeCriterion implements MatchCriterion
{
    public function evaluate(PetReport $report, PetReport $candidate): CriterionResult
    {
        if (! $report->size || ! $candidate->size) {
            return CriterionResult::unknown();
        }

        return $report->size === $candidate->size
            ? CriterionResult::full('Mismo tamaño')
            : CriterionResult::none();
    }
}
