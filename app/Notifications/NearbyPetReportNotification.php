<?php

namespace App\Notifications;

use App\Models\PetReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NearbyPetReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly PetReport $report)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->report->type === 'perdida'
            ? 'Se perdió una mascota cerca de ti'
            : 'Se encontró una mascota cerca de ti';

        $message = (new MailMessage)
            ->subject($title)
            ->greeting($title)
            ->line($this->report->pet_name
                ? "Mascota: {$this->report->pet_name}"
                : 'Mascota sin nombre registrado')
            ->line($this->report->description);

        if ($this->report->location_reference) {
            $message->line("Referencia de ubicación: {$this->report->location_reference}");
        }

        $message
            ->action('Ver el reporte y colaborar', route('reports.show', $this->report->management_token))
            ->line('Recibes esto porque activaste avisos de reportes cercanos en PetRescue.');

        if ($notifiable instanceof \App\Models\Subscriber) {
            $message->line('¿Ya no quieres recibir estos avisos? '
                .route('subscribers.unsubscribe', $notifiable->unsubscribe_token));
        }

        return $message;
    }
}
