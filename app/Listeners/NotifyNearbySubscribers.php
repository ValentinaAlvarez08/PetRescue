<?php

namespace App\Listeners;

use App\Events\PetReportPublished;
use App\Models\Subscriber;
use App\Notifications\NearbyPetReportNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * HU3 (CID 1): busca a los suscriptores dentro del radio configurado por
 * cada uno y les envía la notificación del nuevo reporte.
 */
class NotifyNearbySubscribers implements ShouldQueue
{
    public function handle(PetReportPublished $event): void
    {
        $report = $event->report;

        Subscriber::near((float) $report->latitude, (float) $report->longitude)
            ->each(fn (Subscriber $subscriber) => $subscriber->notify(new NearbyPetReportNotification($report)));
    }
}
