<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\PetReport;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Sprint 4 — HU-20: avisa a moderación que un contenido se ocultó por
 * denuncias. El enlace firmado permite restaurarlo si no infringe nada.
 */
class ContentHiddenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly PetReport|Post|Comment $content)
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
        $content = $this->content;

        $message = (new MailMessage)
            ->subject('Contenido oculto por denuncias en PetRescue')
            ->greeting('Se ocultó '.$content->flagLabel())
            ->line('Llegó a '.config('community.flags.hide_threshold').' denuncias de personas distintas y quedó oculto para la comunidad. Motivos:');

        foreach ($content->flags()->latest()->get() as $flag) {
            $message->line("• {$flag->reason_label}".($flag->details ? ": {$flag->details}" : ''));
        }

        return $message
            ->action('Revisar el contenido', self::reviewUrl($content))
            ->line('Si no infringe las normas, desde ese enlace puedes volver a mostrarlo.');
    }

    public static function reviewUrl(PetReport|Post|Comment $content): string
    {
        return URL::temporarySignedRoute(
            'moderation.flagged.show',
            now()->addDays(config('community.moderation.link_days')),
            ['type' => $content->getMorphClass(), 'id' => $content->getKey()],
        );
    }
}
