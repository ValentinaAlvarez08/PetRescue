<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\PetReport;
use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        // Lo mismo aplica a NotifyPossibleMatches (HU-08).

        // HU-20: nombres cortos en content_flags.flaggable_type (y en los
        // enlaces de moderación) en vez del nombre completo de la clase.
        Relation::enforceMorphMap([
            'reporte' => PetReport::class,
            'publicacion' => Post::class,
            'comentario' => Comment::class,
        ]);
    }
}
