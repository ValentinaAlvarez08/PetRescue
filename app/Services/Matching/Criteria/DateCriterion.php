<?php

namespace App\Services\Matching\Criteria;

use App\Contracts\MatchCriterion;
use App\Models\PetReport;
use App\Services\Matching\CriterionResult;
use Illuminate\Support\Carbon;

/**
 * Fechas: descarta la pareja si se encontró antes de perderse (con unos días
 * de margen) o si hay demasiados días entre una y otra.
 */
class DateCriterion implements MatchCriterion
{
    public function __construct(
        private readonly int $maxDaysApart,
        private readonly int $graceDays = 3,
        private readonly int $closeDays = 7,
    ) {
    }

    public function evaluate(PetReport $report, PetReport $candidate): CriterionResult
    {
        [$lost, $found] = $report->type === 'perdida' ? [$report, $candidate] : [$candidate, $report];
        $daysApart = $this->daysApart($lost, $found);

        if ($daysApart === null) {
            return CriterionResult::unknown();
        }

        if ($daysApart < -$this->graceDays || $daysApart > $this->maxDaysApart) {
            return CriterionResult::reject();
        }

        $days = abs($daysApart);

        if ($days > $this->closeDays) {
            return CriterionResult::unknown();
        }

        return CriterionResult::full($days <= 1 ? 'Fechas del mismo día o seguidas' : "Con {$days} días de diferencia");
    }

    /** Días entre que se perdió y que se encontró (negativo = se encontró antes). */
    private function daysApart(PetReport $lost, PetReport $found): ?int
    {
        $lostDate = $lost->event_date ?? $lost->created_at;
        $foundDate = $found->event_date ?? $found->created_at;

        if (! $lostDate || ! $foundDate) {
            return null;
        }

        return (int) Carbon::parse($lostDate)->startOfDay()->diffInDays(Carbon::parse($foundDate)->startOfDay(), false);
    }
}
