<?php

namespace App\Events;

use App\Models\PetReport;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * HU3: se dispara cuando se publica un nuevo reporte (perdida/encontrada)
 * para que los suscriptores cercanos reciban un aviso automático.
 */
class PetReportPublished
{
    use Dispatchable;

    public function __construct(public readonly PetReport $report)
    {
    }
}
