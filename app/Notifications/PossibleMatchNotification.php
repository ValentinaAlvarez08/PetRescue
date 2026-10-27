<?php

namespace App\Notifications;

use App\Models\PetReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

/**
 * Sprint 4 — HU-08: aviso a quien publicó $ownReport de que $match podría
 * ser la misma mascota. El puntaje y las razones llegan aparte porque el
 * modelo se vuelve a cargar al salir de la cola (sin esos datos calculados).
 */
class PossibleMatchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, string>  $reasons
     */
    public function __construct(
        public readonly PetReport $ownReport,
        public readonly PetReport $match,
        public readonly int $score,
        public readonly array $reasons = [],
    ) {
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
        $own = $this->ownReport;
        $match = $this->match;

        $title = $own->type === 'perdida'
            ? "Alguien encontró una mascota ({$match->species_label}) que se parece a {$own->display_name}"
            : "Alguien está buscando una mascota ({$match->species_label}) que se parece a la que encontraste";

        $message = (new MailMessage)
            ->subject("Posible coincidencia con tu reporte de {$own->display_name}")
            ->greeting($title);

        if ($match->photo_url) {
            $message->line(new HtmlString('<img src="'.e(url($match->photo_url)).'" alt="Foto de '.e($match->display_name)
                .'" style="width:100%;max-height:320px;object-fit:cover;border-radius:12px">'));
        }

        $message->line("Coincidencia: {$this->score}%");

        foreach ($this->reasons as $reason) {
            $message->line("• {$reason}");
        }

        if ($match->location_reference) {
            $message->line("Referencia de ubicación: {$match->location_reference}");
        }

        return $message
            ->line($match->description)
            ->action('Ver el reporte y contactar', $match->public_url)
            ->line('Antes de entregar o recibir una mascota, verifica que sea la misma: pide fotos antiguas, señas que no estén en el reporte o el carné de vacunas.')
            ->line("Administra tu reporte (enlace privado, no lo compartas): ".route('reports.manage', $own->management_token));
    }
}
