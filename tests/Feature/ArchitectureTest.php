<?php

namespace Tests\Feature;

use App\Contracts\DistanceCalculator;
use App\Contracts\Flaggable;
use App\Contracts\MatchCriterion;
use App\Contracts\PhotoStorage;
use App\Contracts\ReportMatcher;
use App\Models\Comment;
use App\Models\PetReport;
use App\Models\Post;
use App\Notifications\ContentHiddenNotification;
use App\Services\Geo\HaversineDistance;
use App\Services\Geo\NearbyFinder;
use App\Services\Matching\Criteria\BreedCriterion;
use App\Services\Matching\Criteria\ColorCriterion;
use App\Services\Matching\Criteria\DateCriterion;
use App\Services\Matching\Criteria\DistanceCriterion;
use App\Services\Matching\Criteria\SexCriterion;
use App\Services\Matching\Criteria\SizeCriterion;
use App\Services\Matching\CriterionResult;
use App\Services\Matching\WeightedReportMatcher;
use App\Services\Moderation\ModerationNotifier;
use App\Services\Photos\PublicDiskPhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Pruebas de la refactorización SOLID: contratos, inyección de dependencias,
 * criterios de coincidencia configurables, búsqueda geográfica, fotos y
 * límites de envíos.
 */
class ArchitectureTest extends TestCase
{
    use RefreshDatabase;

    private const CENTRO = [1.2136, -77.2811];

    private function report(array $attributes = []): PetReport
    {
        return PetReport::factory()->create(array_merge([
            'latitude' => self::CENTRO[0],
            'longitude' => self::CENTRO[1],
            'status' => 'activo',
        ], $attributes));
    }

    // ---------- D: el contenedor entrega las implementaciones ----------

    public function test_container_resolves_every_contract(): void
    {
        $this->assertInstanceOf(WeightedReportMatcher::class, app(ReportMatcher::class));
        $this->assertInstanceOf(HaversineDistance::class, app(DistanceCalculator::class));
        $this->assertInstanceOf(PublicDiskPhotoStorage::class, app(PhotoStorage::class));
    }

    public function test_report_matcher_implementation_can_be_swapped(): void
    {
        $fake = new class implements ReportMatcher
        {
            public function for(PetReport $report, ?int $limit = null): Collection
            {
                return collect(['falso']);
            }
        };
        $this->app->instance(ReportMatcher::class, $fake);

        $this->assertSame(['falso'], $this->report()->similarReports()->all());
    }

    // ---------- I / L: un solo contrato para todo lo denunciable ----------

    public function test_flaggable_models_share_the_contract(): void
    {
        foreach ([PetReport::class, Post::class, Comment::class] as $class) {
            $this->assertTrue(is_subclass_of($class, Flaggable::class), "{$class} debe implementar Flaggable");
        }
    }

    public function test_moderation_rejects_types_outside_the_flaggable_map(): void
    {
        $url = URL::temporarySignedRoute('moderation.flagged.show', now()->addDay(), ['type' => 'negocio', 'id' => 1]);

        $this->get($url)->assertNotFound();
    }

    // ---------- O: criterios y pesos configurables ----------

    public function test_each_criterion_scores_independently(): void
    {
        $lost = new PetReport(['type' => 'perdida', 'color' => 'negro,blanco', 'breed' => 'Criollo', 'sex' => 'macho', 'size' => 'mediano', 'event_date' => '2026-10-01']);
        $found = new PetReport(['type' => 'encontrada', 'color' => 'Negro', 'breed' => 'criollo', 'sex' => 'no_se', 'size' => 'grande', 'event_date' => '2026-10-02']);
        $found->distance_km = 2.5;

        $this->assertEqualsWithDelta(0.75, (new DistanceCriterion(10))->evaluate($lost, $found)->fraction, 0.001);
        $this->assertSame(0.5, (new ColorCriterion())->evaluate($lost, $found)->fraction);
        $this->assertSame(1.0, (new BreedCriterion())->evaluate($lost, $found)->fraction);
        $this->assertSame(0.5, (new SexCriterion())->evaluate($lost, $found)->fraction);
        $this->assertSame(0.0, (new SizeCriterion())->evaluate($lost, $found)->fraction);
        $this->assertSame('Fechas del mismo día o seguidas', (new DateCriterion(60))->evaluate($lost, $found)->reason);
    }

    public function test_date_criterion_rejects_found_before_lost(): void
    {
        $lost = new PetReport(['type' => 'perdida', 'event_date' => '2026-10-20']);
        $found = new PetReport(['type' => 'encontrada', 'event_date' => '2026-10-01']);

        $this->assertTrue((new DateCriterion(60))->evaluate($lost, $found)->rejected);
    }

    public function test_weights_come_from_config(): void
    {
        $lost = $this->report(['type' => 'perdida', 'species' => 'perro', 'color' => 'negro', 'event_date' => now()->subDay()]);
        $this->report(['type' => 'encontrada', 'species' => 'perro', 'color' => 'negro', 'event_date' => now()]);

        // Solo cuenta el color: mismo color = 100 puntos.
        config([
            'pets.matching.weights' => ['distance' => 0, 'colors' => 100, 'breed' => 0, 'sex' => 0, 'size' => 0, 'dates' => 0],
            'pets.matching.min_score' => 100,
        ]);

        $this->assertSame([100], $lost->similarReports()->pluck('match_score')->all());
    }

    public function test_new_criterion_can_be_added_without_touching_the_matcher(): void
    {
        $lost = $this->report(['type' => 'perdida', 'species' => 'gato', 'event_date' => now()->subDay()]);
        $this->report(['type' => 'encontrada', 'species' => 'gato', 'event_date' => now()]);

        $always = new class implements MatchCriterion
        {
            public function evaluate(PetReport $report, PetReport $candidate): CriterionResult
            {
                return CriterionResult::full('Criterio nuevo');
            }
        };

        $matcher = new WeightedReportMatcher(app(NearbyFinder::class), ['nuevo' => $always], ['nuevo' => 80], 10, 50, 4);
        $match = $matcher->for($lost)->first();

        $this->assertSame(80, $match->match_score);
        $this->assertSame(['Criterio nuevo'], $match->match_reasons);
    }

    // ---------- S: servicios con una sola responsabilidad ----------

    public function test_nearby_finder_filters_by_radius_and_sorts_by_distance(): void
    {
        $far = $this->report(['latitude' => 1.2496]);   // ~4 km
        $near = $this->report(['latitude' => 1.2226]);  // ~1 km
        $this->report(['latitude' => 1.4836]);          // ~30 km, fuera

        $found = app(NearbyFinder::class)->within(PetReport::query(), self::CENTRO[0], self::CENTRO[1], 5);

        $this->assertSame([$near->id, $far->id], $found->pluck('id')->all());
        $this->assertEqualsWithDelta(1.0, $found->first()->distance_km, 0.05);
    }

    public function test_haversine_distance(): void
    {
        $this->assertEqualsWithDelta(1.0, (new HaversineDistance())->kilometers(1.2136, -77.2811, 1.2226, -77.2811), 0.01);
    }

    public function test_photo_storage_keeps_sample_photos_and_copies_uploads(): void
    {
        Storage::fake('public');
        $photos = app(PhotoStorage::class);

        $path = $photos->store(UploadedFile::fake()->image('luna.jpg'), 'pet-profiles');
        $copy = $photos->copy($path, 'pet-reports');

        Storage::disk('public')->assertExists([$path, $copy]);
        $this->assertStringStartsWith('pet-reports/', $copy);

        $this->assertSame('images/seed/perro.jpg', $photos->copy('images/seed/perro.jpg', 'pet-reports'));
        $photos->delete('images/seed/perro.jpg'); // no hace nada
        $photos->delete($path);
        Storage::disk('public')->assertMissing($path);
        $this->assertNull($photos->url(null));
    }

    public function test_moderation_notifier_needs_an_email(): void
    {
        Notification::fake();
        $notification = new ContentHiddenNotification($this->report());

        config(['community.moderation.email' => null]);
        $this->assertFalse(app(ModerationNotifier::class)->notify($notification));

        config(['community.moderation.email' => 'moderacion@petrescue.test']);
        $this->assertTrue(app(ModerationNotifier::class)->notify($notification));
        Notification::assertSentOnDemandTimes(ContentHiddenNotification::class, 1);
    }

    // ---------- Seguridad: límite de envíos sin login ----------

    public function test_public_api_is_rate_limited(): void
    {
        $url = route('reports.nearby', ['lat' => self::CENTRO[0], 'lng' => self::CENTRO[1]]);

        for ($i = 0; $i < 60; $i++) {
            $this->getJson($url)->assertOk();
        }

        $this->getJson($url)->assertStatus(429);
    }

    public function test_comment_form_is_rate_limited(): void
    {
        $post = Post::factory()->create(['topic' => 'salud']);
        $url = route('comments.store', ['salud', $post]);

        for ($i = 0; $i < 30; $i++) {
            $this->post($url, ['nickname' => 'Ana', 'body' => 'Comentario de prueba número '.$i]);
        }

        $this->post($url, ['nickname' => 'Ana', 'body' => 'Uno más de la cuenta'])->assertStatus(429);
    }
}
