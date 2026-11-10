<?php

namespace App\Contracts;

use App\Models\PetReport;
use Illuminate\Support\Collection;

/**
 * HU-05 / HU-08: busca reportes del tipo contrario que podrían ser la misma
 * mascota. Cada resultado trae match_score, match_reasons y distance_km.
 */
interface ReportMatcher
{
    /**
     * @return Collection<int, PetReport>
     */
    public function for(PetReport $report, ?int $limit = null): Collection;
}
