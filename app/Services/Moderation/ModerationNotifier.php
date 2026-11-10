<?php

namespace App\Services\Moderation;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Notification;

/**
 * HU-19 / HU-20: avisos al correo de moderación (MODERATION_EMAIL).
 * Si no hay correo configurado no se envía nada; los enlaces se pueden
 * obtener con `php artisan moderacion:pendientes`.
 */
class ModerationNotifier
{
    public function __construct(private readonly Config $config)
    {
    }

    public function email(): ?string
    {
        return $this->config->get('community.moderation.email') ?: null;
    }

    /** Devuelve true si el aviso se envió. */
    public function notify(BaseNotification $notification): bool
    {
        if (! $email = $this->email()) {
            return false;
        }

        Notification::route('mail', $email)->notify($notification);

        return true;
    }
}
