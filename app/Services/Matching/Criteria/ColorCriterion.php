<?php

namespace App\Services\Matching\Criteria;

use App\Contracts\MatchCriterion;
use App\Models\PetReport;
use App\Services\Matching\CriterionResult;

/** Proporción de colores en común. */
class ColorCriterion implements MatchCriterion
{
    public function evaluate(PetReport $report, PetReport $candidate): CriterionResult
    {
        $mine = $report->normalizedColorKeys();
        $theirs = $candidate->normalizedColorKeys();
        $shared = array_values(array_intersect($mine, $theirs));

        if (! $shared) {
            return CriterionResult::none();
        }

        $labels = array_map(fn ($key) => config("pets.colors.{$key}.label", $key), $shared);

        return CriterionResult::partial(
            count($shared) / max(count($mine), count($theirs)),
            (count($shared) === 1 ? 'Mismo color: ' : 'Mismos colores: ').implode(', ', $labels),
        );
    }
}
