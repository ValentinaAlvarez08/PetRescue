<?php

namespace App\Services\Matching;

use App\Contracts\MatchCriterion;
use App\Contracts\ReportMatcher;
use App\Models\PetReport;
use App\Services\Geo\NearbyFinder;
use Illuminate\Support\Collection;

/**
 * Sprint 4 — HU-05 / HU-08: busca reportes que podrían ser la misma mascota.
 *
 * Una perdida se compara con las encontradas (y al revés) que siguen activas,
 * son de la misma especie y están dentro del radio configurado. Cada
 * candidata recibe un puntaje: la suma de peso × fracción de cada criterio.
 * Los criterios y sus pesos están en config('pets.matching'), así que se
 * agregan, quitan o ajustan sin modificar esta clase.
 */
class WeightedReportMatcher implements ReportMatcher
{
    /**
     * @param  array<string, MatchCriterion>  $criteria  llave => criterio
     * @param  array<string, float|int>  $weights  llave => peso
     */
    public function __construct(
        private readonly NearbyFinder $nearby,
        private readonly array $criteria,
        private readonly array $weights,
        private readonly float $radiusKm,
        private readonly int $minScore,
        private readonly int $defaultLimit,
    ) {
    }

    public function for(PetReport $report, ?int $limit = null): Collection
    {
        if (! $report->species || $report->latitude === null || $report->longitude === null) {
            return collect();
        }

        $query = PetReport::query()
            ->active()
            ->ofType($report->type === 'perdida' ? 'encontrada' : 'perdida')
            ->where('species', $report->species);

        if ($report->exists) {
            $query->whereKeyNot($report->getKey());
        }

        return $this->nearby->within($query, (float) $report->latitude, (float) $report->longitude, $this->radiusKm)
            ->filter(fn (PetReport $candidate) => $this->score($report, $candidate))
            ->filter(fn (PetReport $candidate) => $candidate->match_score >= $this->minScore)
            ->sortByDesc('match_score')
            ->take($limit ?? $this->defaultLimit)
            ->values();
    }

    /**
     * Guarda match_score y match_reasons en la candidata. Devuelve false si
     * algún criterio la descarta.
     */
    private function score(PetReport $report, PetReport $candidate): bool
    {
        $score = 0.0;
        $reasons = [];

        foreach ($this->criteria as $key => $criterion) {
            $result = $criterion->evaluate($report, $candidate);

            if ($result->rejected) {
                return false;
            }

            $score += ($this->weights[$key] ?? 0) * $result->fraction;

            if ($result->reason !== null) {
                $reasons[] = $result->reason;
            }
        }

        $candidate->match_score = (int) round($score);
        $candidate->match_reasons = $reasons;

        return true;
    }
}
