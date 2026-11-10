<?php

namespace App\Notifications;

use App\Models\PetReport;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class NearbyPetReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * HU-11 (CID 3): un solo correo por reporte aunque caiga en varias zonas
     * del mismo suscriptor; $zones trae todas las que lo cubren.
     *
     * @param  array<int, array{label: string, distance_km: float, unsubscribe_url: string}>  $zones
     */
    public function __construct(
        public readonly PetReport $report,
        public readonly array $zones = [],
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * HU-11 (CID 2): datos de la mascota y enlace al reporte.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $report = $this->report;
        $lost = $report->type === 'perdida';
        $zoneNames = collect($this->zones)->pluck('label')->map(fn ($label) => "«{$label}»")->implode(', ', ' y ');

        $title = ($lost ? 'Se perdió una mascota' : 'Se encontró una mascota')
            .($zoneNames ? " cerca de {$zoneNames}" : ' cerca de ti');

        $message = (new MailMessage)
            ->subject("{$title}: {$report->display_name} ({$report->species_label})")
            ->greeting($title);

        if ($report->photo_url) {
            $message->line(new HtmlString('<img src="'.e(url($report->photo_url)).'" alt="Foto de '.e($report->display_name)
                .'" style="width:100%;max-height:320px;object-fit:cover;border-radius:12px">'));
        }

        $message->line($report->pet_name ? "Mascota: {$report->pet_name}" : 'Mascota sin nombre registrado')
            ->line("Especie: {$report->species_label}".($report->breed ? " · {$report->breed}" : ''));

        if ($report->color_labels) {
            $message->line("Color: {$report->color_labels}");
        }
        if ($report->sex || $report->size) {
            $message->line('Sexo · tamaño: '.config('pets.sexes.'.$report->sex, '—').($report->size ? ' · '.ucfirst($report->size) : ''));
        }
        if ($report->event_date) {
            $message->line(($lost ? 'Se perdió el ' : 'Se encontró el ').$report->event_date->format('d/m/Y'));
        }
        if ($report->location_reference) {
            $message->line("Referencia de ubicación: {$report->location_reference}");
        }

        foreach ($this->zones as $zone) {
            $message->line("A ".number_format($zone['distance_km'], 1)." km de tu zona «{$zone['label']}».");
        }

        $message
            ->line($report->description)
            ->action('Ver el reporte y colaborar', $report->public_url)
            ->line('Recibes esto porque activaste alertas de zonas en PetRescue.');

        if ($notifiable instanceof Subscriber) {
            $message->line('Administrar todas tus zonas: '.route('subscribers.manage', $notifiable->manage_token));
        }

        // HU-11 (CID 4): la baja es por zona.
        foreach ($this->zones as $zone) {
            $message->line("¿Ya no quieres avisos de «{$zone['label']}»? {$zone['unsubscribe_url']}");
        }

        return $message;
    }
}
