<?php

namespace App\Listeners;

use App\Events\PetReportPublished;
use App\Events\PetReportUpdated;
use App\Models\PetReport;
use App\Models\ReportMatch;
use App\Notifications\PossibleMatchNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Sprint 4 — HU-08: cuando se publica (o edita) un reporte, avisa por correo
 * a quienes publicaron reportes que podrían ser la misma mascota.
 *
 * - Solo se avisa a reportes con correo de contacto (es opcional).
 * - Cada par perdida ↔ encontrada se avisa una sola vez (report_matches).
 * - No se avisa si ambos reportes tienen el mismo celular (misma persona).
 * - Quien acaba de publicar ve las coincidencias en su enlace privado (HU-05).
 */
class NotifyPossibleMatches implements ShouldQueue
{
    public function handle(PetReportPublished|PetReportUpdated $event): void
    {
        $report = $event->report->fresh();

        if (! $report || $report->status !== 'activo' || $report->isHidden()) {
            return;
        }

        foreach ($report->similarReports() as $match) {
            [$lost, $found] = $report->type === 'perdida' ? [$report, $match] : [$match, $report];

            $pair = ReportMatch::firstOrCreate(
                ['lost_report_id' => $lost->id, 'found_report_id' => $found->id],
                ['score' => $match->match_score],
            );

            if (! $pair->wasRecentlyCreated || ! $match->contact_email || $match->contact_phone === $report->contact_phone) {
                continue;
            }

            Notification::route('mail', $match->contact_email)->notify(new PossibleMatchNotification(
                $match,
                $report,
                $match->match_score,
                $match->match_reasons,
            ));

            $pair->update(['notified_at' => now()]);
        }
    }
}
