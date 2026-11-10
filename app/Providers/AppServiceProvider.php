<?php

namespace App\Providers;

use App\Contracts\DistanceCalculator;
use App\Contracts\PhotoStorage;
use App\Contracts\ReportMatcher;
use App\Models\Comment;
use App\Models\PetReport;
use App\Models\Post;
use App\Services\Geo\HaversineDistance;
use App\Services\Geo\NearbyFinder;
use App\Services\Matching\Criteria\BreedCriterion;
use App\Services\Matching\Criteria\ColorCriterion;
use App\Services\Matching\Criteria\DateCriterion;
use App\Services\Matching\Criteria\DistanceCriterion;
use App\Services\Matching\Criteria\SexCriterion;
use App\Services\Matching\Criteria\SizeCriterion;
use App\Services\Matching\WeightedReportMatcher;
use App\Services\Photos\PublicDiskPhotoStorage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Inversión de dependencias: el resto de la aplicación pide las
     * interfaces de App\Contracts y aquí se decide qué implementación usar.
     * Para cambiar una (por ejemplo guardar las fotos en S3 o probar otro
     * algoritmo de coincidencias) solo se toca este archivo.
     */
    public function register(): void
    {
        $this->app->singleton(DistanceCalculator::class, HaversineDistance::class);
        $this->app->singleton(NearbyFinder::class);

        // "public" en local, "supabase" en producción (config/filesystems.php).
        $this->app->singleton(PhotoStorage::class, fn (Application $app) => new PublicDiskPhotoStorage(
            $app['config']->get('filesystems.photos_disk', 'public')
        ));

        // Se arma en cada uso (bind, no singleton) para leer siempre la
        // configuración vigente de config('pets.matching').
        $this->app->bind(ReportMatcher::class, function (Application $app) {
            $config = $app['config']->get('pets.matching');

            return new WeightedReportMatcher(
                nearby: $app->make(NearbyFinder::class),
                criteria: [
                    'distance' => new DistanceCriterion((float) $config['radius_km']),
                    'colors' => new ColorCriterion(),
                    'breed' => new BreedCriterion(),
                    'sex' => new SexCriterion(),
                    'size' => new SizeCriterion(),
                    'dates' => new DateCriterion((int) $config['max_days_apart']),
                ],
                weights: $config['weights'],
                radiusKm: (float) $config['radius_km'],
                minScore: (int) $config['min_score'],
                defaultLimit: (int) $config['limit'],
            );
        });
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
        // Cada modelo de esta lista implementa App\Contracts\Flaggable.
        Relation::enforceMorphMap([
            'reporte' => PetReport::class,
            'publicacion' => Post::class,
            'comentario' => Comment::class,
        ]);

        $this->configureRateLimiting();
    }

    /**
     * Sin login, la única defensa contra el spam es limitar por IP cuántas
     * veces se puede enviar un formulario o consultar la API.
     */
    private function configureRateLimiting(): void
    {
        // Publicar reportes, perfiles, publicaciones, negocios y suscripciones.
        RateLimiter::for('publicar', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // Comentarios y "Me sirvió" (acciones cortas y más frecuentes).
        RateLimiter::for('interactuar', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        // HU-20: denuncias.
        RateLimiter::for('denunciar', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // API JSON del radar y de reportes similares (el formulario la consulta varias veces).
        RateLimiter::for('api-publica', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
    }
}
