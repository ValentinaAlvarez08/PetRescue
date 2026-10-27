<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Sprint 4 — HU-19: avisa a moderación que hay un negocio por revisar. El
 * enlace está firmado (no hay login de administrador) y vence en unos días.
 */
class BusinessRequestedNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject("Nuevo negocio por revisar: {$business->name}")
            ->greeting('Hay un negocio que quiere entrar al directorio')
            ->line("Negocio: {$business->name} ({$business->category_info['short']})")
            ->line("Dirección: {$business->address}")
            ->line("Teléfono: {$business->phone}")
            ->line("Lo solicita: {$business->requester_name} <{$business->requester_email}>")
            ->line($business->description)
            ->action('Revisar y aprobar o rechazar', self::reviewUrl($business));
    }

    public static function reviewUrl(Business $business): string
    {
        return URL::temporarySignedRoute(
            'moderation.businesses.show',
            now()->addDays(config('community.moderation.link_days')),
            ['business' => $business->id],
        );
    }
}
