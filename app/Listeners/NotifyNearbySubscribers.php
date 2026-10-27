<?php

namespace App\Listeners;

use App\Events\PetReportPublished;
use App\Models\AlertZone;
use App\Notifications\NearbyPetReportNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * HU3 / HU-11: busca las zonas cuyo radio cubre el nuevo reporte y avisa a
 * sus suscriptores. Las zonas se agrupan por suscriptor (CID 3): quien vigila
 * casa y trabajo y el reporte cae en ambas recibe un solo correo.
 */
class NotifyNearbySubscribers implements ShouldQueue
{
    public function handle(PetReportPublished $event): void
    {
        $report = $event->report;

        AlertZone::covering((float) $report->latitude, (float) $report->longitude)
            ->groupBy('subscriber_id')
            ->each(function ($zones) use ($report) {
                $zones = $zones->sortBy('distance_km')->values();

                $zones->first()->subscriber->notify(new NearbyPetReportNotification(
                    $report,
                    $zones->map(fn (AlertZone $zone) => [
                        'label' => $zone->label,
                        'distance_km' => round($zone->distance_km, 2),
                        'unsubscribe_url' => route('subscribers.unsubscribe', $zone->unsubscribe_token),
                    ])->all(),
                ));
            });
    }
}
