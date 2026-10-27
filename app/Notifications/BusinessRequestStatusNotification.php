<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sprint 4 — HU-19: le cuenta a quien pidió el registro en qué va su
 * solicitud (recibida, aprobada o rechazada).
 */
class BusinessRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Business $business)
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
        $business = $this->business;
        $message = (new MailMessage)->greeting("Hola, {$business->requester_name}");

        return match ($business->review_status) {
            'aprobado' => $message
                ->subject("¡{$business->name} ya está en el directorio de PetRescue!")
                ->line("Aprobamos la solicitud: «{$business->name}» ya aparece en el directorio y en el mapa.")
                ->action('Ver el directorio', route('directory.index', ['categoria' => $business->category])),
            'rechazado' => $message
                ->subject("Tu solicitud para {$business->name} no fue aprobada")
                ->line("Revisamos la solicitud de «{$business->name}» y por ahora no podemos agregarlo al directorio.")
                ->line($business->review_note ? "Motivo: {$business->review_note}" : 'Si crees que es un error, puedes enviar una nueva solicitud con los datos corregidos.'),
            default => $message
                ->subject("Recibimos la solicitud de {$business->name}")
                ->line("Recibimos la solicitud para agregar «{$business->name}» al directorio de PetRescue.")
                ->line('La revisaremos para confirmar que sea un servicio para mascotas y te escribiremos cuando esté publicado.'),
        };
    }
}
