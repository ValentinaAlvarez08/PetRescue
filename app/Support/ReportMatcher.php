<?php

namespace App\Support;

use App\Models\PetReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Sprint 4 — HU-05 / HU-08: busca reportes que podrían ser la misma mascota.
 *
 * Una perdida se compara con las encontradas (y al revés) que siguen activas,
 * son de la misma especie y están dentro de config('pets.matching.radius_km').
 * Cada candidata recibe un puntaje de 0 a 100:
 *
 *   distancia 25 · colores 30 · raza 15 · sexo 10 · tamaño 10 · fechas 10
 *
 * Un dato que alguno de los dos no sabe (raza "no sé", sexo "no sé", sin
 * tamaño) suma la mitad: no confirma, pero tampoco descarta.
 * Solo se sugieren las que llegan a min_score.
 */
class ReportMatcher
{
    /**
     * Funciona también con un reporte sin guardar (el formulario de HU-05
     * consulta antes de publicar). Cada resultado trae match_score,
     * match_reasons y distance_km.
     *
     * @return Collection<int, PetReport>
     */
    public static function for(PetReport $report, ?int $limit = null): Collection
    {
        $config = config('pets.matching');

        if (! $report->species || $report->latitude === null || $report->longitude === null) {
            return collect();
        }

        $query = PetReport::query()
            ->active()
            ->where('type', $report->type === 'perdida' ? 'encontrada' : 'perdida')
            ->where('species', $report->species);

        if ($report->exists) {
            $query->whereKeyNot($report->getKey());
        }

        return PetReport::near($query, (float) $report->latitude, (float) $report->longitude, $config['radius_km'])
            ->filter(fn (PetReport $candidate) => self::score($report, $candidate, $config))
            ->filter(fn (PetReport $candidate) => $candidate->match_score >= $config['min_score'])
            ->sortByDesc('match_score')
            ->take($limit ?? $config['limit'])
            ->values();
    }

    /**
     * Calcula el puntaje y las razones legibles. Devuelve false si las fechas
     * hacen imposible que sea la misma mascota.
     */
    private static function score(PetReport $report, PetReport $candidate, array $config): bool
    {
        [$lost, $found] = $report->type === 'perdida' ? [$report, $candidate] : [$candidate, $report];
        $reasons = [];

        $daysApart = self::daysApart($lost, $found);
        // Se encontró antes de perderse (con 3 días de margen) o muy lejos en el tiempo.
        if ($daysApart !== null && ($daysApart < -3 || $daysApart > $config['max_days_apart'])) {
            return false;
        }

        // Distancia: 25 puntos en el mismo punto, 0 en el borde del radio.
        $score = 25 * max(0, 1 - $candidate->distance_km / $config['radius_km']);
        $reasons[] = 'A '.number_format($candidate->distance_km, 1).' km';

        // Colores: proporción de colores en común.
        $mine = self::colors($report);
        $theirs = self::colors($candidate);
        $shared = array_values(array_intersect($mine, $theirs));
        if ($shared && $mine && $theirs) {
            $score += 30 * count($shared) / max(count($mine), count($theirs));
            $labels = array_map(fn ($key) => config("pets.colors.{$key}.label", $key), $shared);
            $reasons[] = (count($shared) === 1 ? 'Mismo color: ' : 'Mismos colores: ').implode(', ', $labels);
        }

        // Raza (la persona que encuentra muchas veces no la sabe).
        if (! $report->breed || ! $candidate->breed) {
            $score += 7.5;
        } elseif (mb_strtolower($report->breed) === mb_strtolower($candidate->breed)) {
            $score += 15;
            $reasons[] = "Misma raza: {$candidate->breed}";
        }

        // Sexo.
        $unknownSex = fn (?string $sex) => ! $sex || $sex === 'no_se';
        if ($unknownSex($report->sex) || $unknownSex($candidate->sex)) {
            $score += 5;
        } elseif ($report->sex === $candidate->sex) {
            $score += 10;
            $reasons[] = 'Mismo sexo';
        }

        // Tamaño (solo los perros lo tienen).
        if (! $report->size || ! $candidate->size) {
            $score += 5;
        } elseif ($report->size === $candidate->size) {
            $score += 10;
            $reasons[] = 'Mismo tamaño';
        }

        // Fechas.
        if ($daysApart === null) {
            $score += 5;
        } elseif (abs($daysApart) <= 7) {
            $score += 10;
            $reasons[] = abs($daysApart) <= 1 ? 'Fechas del mismo día o seguidas' : 'Con '.abs($daysApart).' días de diferencia';
        } else {
            $score += 5;
        }

        $candidate->match_score = (int) round($score);
        $candidate->match_reasons = $reasons;

        return true;
    }

    /** Días entre que se perdió y que se encontró (negativo = se encontró antes). */
    private static function daysApart(PetReport $lost, PetReport $found): ?int
    {
        $lostDate = $lost->event_date ?? $lost->created_at;
        $foundDate = $found->event_date ?? $found->created_at;

        if (! $lostDate || ! $foundDate) {
            return null;
        }

        return (int) Carbon::parse($lostDate)->startOfDay()->diffInDays(Carbon::parse($foundDate)->startOfDay(), false);
    }

    /** @return array<int, string> llaves de color en minúscula */
    private static function colors(PetReport $report): array
    {
        return collect(explode(',', (string) $report->color))
            ->map(fn ($key) => mb_strtolower(trim($key)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
