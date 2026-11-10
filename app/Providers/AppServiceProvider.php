<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // NotifyNearbySubscribers escucha PetReportPublished por el
        // descubrimiento automático de eventos de Laravel (app/Listeners).
        // No se registra aquí a mano: quedaría duplicado y cada suscriptor
        // recibiría dos correos por el mismo reporte (HU-11, CID 3).
    }
}
