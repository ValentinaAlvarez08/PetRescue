<?php

namespace App\Services\Matching\Criteria;

use App\Contracts\MatchCriterion;
use App\Models\PetReport;
use App\Services\Matching\CriterionResult;

class SexCriterion implements MatchCriterion
{
    public function evaluate(PetReport $report, PetReport $candidate): CriterionResult
    {
        if ($this->unknown($report->sex) || $this->unknown($candidate->sex)) {
            return CriterionResult::unknown();
        }

        return $report->sex === $candidate->sex
            ? CriterionResult::full('Mismo sexo')
            : CriterionResult::none();
    }

    private function unknown(?string $sex): bool
    {
        return ! $sex || $sex === 'no_se';
    }
}
