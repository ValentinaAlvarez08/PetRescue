<?php

namespace App\Notifications;

use App\Models\AlertZone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sprint 4 — HU-11 (CID 1): confirma la zona guardada y entrega el enlace
 * privado para administrar todas las zonas del correo.
 */
class AlertSubscriptionConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly AlertZone $zone)
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
        return (new MailMessage)
            ->subject("Activaste las alertas de «{$this->zone->label}» en PetRescue")
            ->greeting('¡Gracias por ser los ojos de tu barrio!')
            ->line("Te avisaremos por correo cuando se reporte una mascota perdida o encontrada a menos de {$this->zone->radius_label} km de la zona «{$this->zone->label}».")
            ->line('Desde este enlace puedes ver todas tus zonas, agregar otras (casa, trabajo, finca...), cambiar el radio o pausarlas. Es privado: no lo compartas.')
            ->action('Administrar mis zonas', route('subscribers.manage', $notifiable->manage_token))
            ->line('Si no fuiste tú, puedes desactivar esta zona aquí: '.route('subscribers.unsubscribe', $this->zone->unsubscribe_token));
    }
}
