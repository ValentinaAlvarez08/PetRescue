<?php

namespace App\Http\Controllers;

use App\Models\PetReport;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Sprint 4 — HU-12: estadísticas e historias de reencuentros.
 */
class StatsController extends Controller
{
    /** Meses que se muestran en el gráfico de reportes por mes. */
    private const MONTHS = 12;

    private const MONTH_NAMES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    public function index(): View
    {
        $reunited = PetReport::where('status', 'reunido')->count();

        return view('stats.index', [
            // CID 1
            'totals' => [
                'reports' => PetReport::count(),
                'reunited' => $reunited,
                'active' => PetReport::active()->count(),
            ],
            // CID 2 y 4: sin reunidos no hay historias que mostrar.
            'stories' => $reunited
                ? PetReport::where('status', 'reunido')->latest('reunited_at')->latest('id')->take(6)->get()
                : collect(),
            // CID 3
            'byMonth' => $this->reportsByMonth(),
            'bySpecies' => $this->reportsBySpecies(),
        ]);
    }

    /**
     * Reportes publicados en cada uno de los últimos 12 meses (incluido el
     * actual). Se agrupa en PHP para que funcione igual en SQLite y MySQL.
     *
     * @return array<int, array{label: string, year: string, total: int}>
     */
    private function reportsByMonth(): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(self::MONTHS - 1);

        $counts = PetReport::where('created_at', '>=', $start)
            ->pluck('created_at')
            ->countBy(fn (Carbon $date) => $date->format('Y-m'));

        return collect(range(0, self::MONTHS - 1))
            ->map(function (int $i) use ($start, $counts) {
                $month = $start->copy()->addMonths($i);

                return [
                    'label' => self::MONTH_NAMES[$month->month - 1],
                    'year' => $month->format('Y'),
                    'total' => (int) ($counts[$month->format('Y-m')] ?? 0),
                ];
            })
            ->all();
    }

    /**
     * Total de reportes por especie, de mayor a menor.
     *
     * @return array<int, array{label: string, emoji: string, total: int}>
     */
    private function reportsBySpecies(): array
    {
        return PetReport::selectRaw('species, count(*) as total')
            ->groupBy('species')
            ->pluck('total', 'species')
            ->map(fn ($total, $species) => [
                'label' => config("pets.species.{$species}.label", ucfirst((string) $species)),
                'emoji' => config("pets.species.{$species}.emoji", '🐾'),
                'total' => (int) $total,
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }
}
