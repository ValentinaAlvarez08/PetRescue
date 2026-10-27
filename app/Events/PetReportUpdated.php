<?php

namespace App\Events;

use App\Models\PetReport;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Sprint 4 — HU-07: quien publicó editó los datos del reporte con su enlace
 * privado. Se vuelven a buscar coincidencias (HU-08), pero no se repiten
 * las alertas de zona (eso solo pasa al publicar).
 */
class PetReportUpdated
{
    use Dispatchable;

    public function __construct(public readonly PetReport $report)
    {
    }
}
