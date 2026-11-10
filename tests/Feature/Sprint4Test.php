<?php

namespace Tests\Feature;

use App\Models\AlertZone;
use App\Models\PetReport;
use App\Models\Subscriber;
use App\Notifications\AlertSubscriptionConfirmedNotification;
use App\Notifications\NearbyPetReportNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Casos de prueba del Sprint 4: HU-11, HU-09 y HU-12.
 */
class Sprint4Test extends TestCase
{
    use RefreshDatabase;

    // Puntos en Pasto: el centro y dos zonas a ~1 km y ~30 km.
    private const CENTRO = [1.2136, -77.2811];
    private const CERCA = [1.2226, -77.2811];
    private const LEJOS = [1.4836, -77.2811];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Notification::fake();
    }

    private function subscribe(array $overrides = [])
    {
        return $this->post(route('subscribers.store'), array_merge([
            'name' => 'Vale',
            'email' => 'vale@example.com',
            'label' => 'Casa',
            'latitude' => self::CERCA[0],
            'longitude' => self::CERCA[1],
            'radius_km' => 5,
        ], $overrides));
    }

    private function publishReport(array $overrides = []): PetReport
    {
        $this->post('/reportar', array_merge([
            'type' => 'perdida',
            'species' => 'perro',
            'breed' => 'Labrador',
            'sex' => 'hembra',
            'size' => 'grande',
            'colors' => ['crema'],
            'event_date' => now()->toDateString(),
            'pet_name' => 'Luna',
            'description' => 'Se perdió cerca del parque, tiene collar rojo.',
            'photo' => UploadedFile::fake()->image('luna.jpg'),
            'contact_phone' => '3001234567',
            'latitude' => self::CENTRO[0],
            'longitude' => self::CENTRO[1],
            'location_reference' => 'Parque Infantil',
        ], $overrides))->assertSessionHasNoErrors();

        return PetReport::latest('id')->first();
    }

    // ---------- HU-11: suscribirse a alertas de varias zonas ----------

    public function test_hu11_cp1_email_zone_and_radius_are_saved_with_confirmation(): void
    {
        $this->get(route('subscribers.create'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="label"', false)
            ->assertSee('name="latitude"', false)
            ->assertSee('min="1" max="20"', false);

        $this->subscribe(['email' => ' Vale@Example.com '])
            ->assertRedirect(route('subscribers.create'))
            ->assertSessionHas('status', fn ($status) => str_contains($status, 'Guardamos la zona «Casa»'));

        $subscriber = Subscriber::sole();
        $this->assertSame('vale@example.com', $subscriber->email);
        $this->assertDatabaseHas('alert_zones', ['subscriber_id' => $subscriber->id, 'label' => 'Casa', 'radius_km' => 5, 'active' => true]);

        // El enlace para administrar las zonas llega por correo, no se muestra en pantalla.
        Notification::assertSentTo($subscriber, AlertSubscriptionConfirmedNotification::class, function ($notification) use ($subscriber) {
            return $notification->toMail($subscriber)->actionUrl === route('subscribers.manage', $subscriber->manage_token);
        });
    }

    public function test_hu11_cp1_radius_must_be_between_1_and_20_km_and_point_is_required(): void
    {
        $this->subscribe(['radius_km' => 0])->assertSessionHasErrors('radius_km');
        $this->subscribe(['radius_km' => 21])->assertSessionHasErrors('radius_km');
        $this->subscribe(['latitude' => null, 'longitude' => null])->assertSessionHasErrors(['latitude', 'longitude']);
        $this->subscribe(['email' => 'no-es-correo'])->assertSessionHasErrors('email');
        $this->subscribe(['label' => ''])->assertSessionHasErrors('label');

        $this->assertDatabaseCount('alert_zones', 0);

        $this->subscribe(['radius_km' => 1])->assertSessionHasNoErrors();
        $this->subscribe(['label' => 'Finca', 'radius_km' => 20])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('alert_zones', 2);
    }

    public function test_hu11_cp2_report_inside_a_zone_sends_email_with_pet_data_and_public_link(): void
    {
        $this->subscribe();
        $far = Subscriber::create(['email' => 'lejos@example.com']);
        $far->zones()->create(['label' => 'Finca', 'latitude' => self::LEJOS[0], 'longitude' => self::LEJOS[1], 'radius_km' => 5]);

        $report = $this->publishReport();
        $subscriber = Subscriber::where('email', 'vale@example.com')->first();

        Notification::assertNotSentTo($far, NearbyPetReportNotification::class);
        Notification::assertSentTo($subscriber, NearbyPetReportNotification::class, function ($notification) use ($subscriber, $report) {
            $mail = $notification->toMail($subscriber);
            $text = collect($mail->introLines)->map(fn ($line) => (string) $line)->implode("\n");

            return $mail->actionUrl === route('reports.show', $report)
                && ! str_contains($mail->actionUrl, $report->management_token)
                && str_contains($mail->subject, 'Luna')
                && str_contains($text, 'Mascota: Luna')
                && str_contains($text, 'Perro · Labrador')
                && str_contains($text, 'Crema / dorado')
                && str_contains($text, 'Parque Infantil')
                && str_contains($text, 'zona «Casa»');
        });
    }

    public function test_hu11_cp3_one_email_per_report_even_if_it_falls_in_several_zones(): void
    {
        $this->subscribe(['label' => 'Casa', 'latitude' => self::CERCA[0]]);
        $this->subscribe(['label' => 'Trabajo', 'latitude' => self::CENTRO[0], 'radius_km' => 2]);
        $this->subscribe(['label' => 'Finca', 'latitude' => self::LEJOS[0]]);

        $subscriber = Subscriber::sole();
        $this->assertCount(3, $subscriber->zones);

        $this->publishReport();

        Notification::assertSentToTimes($subscriber, NearbyPetReportNotification::class, 1);
        Notification::assertSentTo($subscriber, NearbyPetReportNotification::class, function ($notification) {
            $labels = collect($notification->zones)->pluck('label')->all();

            // Se ordenan por cercanía y la finca (fuera del radio) no aparece.
            return $labels === ['Trabajo', 'Casa'];
        });

        // Un reporte que solo cae en la finca también llega (aviso de cada zona).
        $this->publishReport(['latitude' => self::LEJOS[0], 'pet_name' => 'Toby']);
        Notification::assertSentToTimes($subscriber, NearbyPetReportNotification::class, 2);
    }

    public function test_hu11_cp4_unsubscribe_link_disables_only_that_zone(): void
    {
        $this->subscribe(['label' => 'Casa']);
        $this->subscribe(['label' => 'Trabajo']);
        $subscriber = Subscriber::sole();
        [$casa, $trabajo] = $subscriber->zones;

        $this->get(route('subscribers.unsubscribe', $casa->unsubscribe_token))
            ->assertRedirect(route('subscribers.manage', $subscriber->manage_token))
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'zona «Casa»'));

        $this->assertFalse($casa->fresh()->active);
        $this->assertTrue($trabajo->fresh()->active);

        $this->publishReport();
        Notification::assertSentTo($subscriber, NearbyPetReportNotification::class,
            fn ($n) => collect($n->zones)->pluck('label')->all() === ['Trabajo']);

        // Cada correo trae el enlace de baja de cada zona que lo generó.
        Notification::assertSentTo($subscriber, NearbyPetReportNotification::class, function ($n) use ($subscriber, $trabajo) {
            $text = collect($n->toMail($subscriber)->outroLines)->implode("\n");

            return str_contains($text, route('subscribers.unsubscribe', $trabajo->unsubscribe_token))
                && str_contains($text, route('subscribers.manage', $subscriber->manage_token));
        });

        $this->get(route('subscribers.unsubscribe', 'token-inexistente'))->assertNotFound();
    }

    public function test_hu11_zones_are_managed_from_the_private_link(): void
    {
        $this->subscribe(['label' => 'Casa']);
        $subscriber = Subscriber::sole();
        $zone = $subscriber->zones->first();
        $token = $subscriber->manage_token;

        $this->get(route('subscribers.manage', $token))
            ->assertOk()
            ->assertSee('vale@example.com')
            ->assertSee('Casa')
            ->assertSee('noindex', false);

        // Agregar, editar, pausar, reactivar y eliminar.
        $this->post(route('subscribers.zones.store', $token), [
            'label' => 'Trabajo', 'latitude' => self::CENTRO[0], 'longitude' => self::CENTRO[1], 'radius_km' => 3,
        ])->assertRedirect(route('subscribers.manage', $token));
        $this->assertCount(2, $subscriber->zones()->get());

        $this->patch(route('subscribers.zones.update', [$token, $zone->id]), ['label' => 'Casa nueva', 'radius_km' => 12]);
        $this->assertSame('Casa nueva', $zone->fresh()->label);
        $this->assertEquals(12, $zone->fresh()->radius_km);

        $this->patch(route('subscribers.zones.update', [$token, $zone->id]), ['radius_km' => 25])->assertSessionHasErrors('radius_km');

        $this->patch(route('subscribers.zones.update', [$token, $zone->id]), ['active' => 0]);
        $this->assertFalse($zone->fresh()->active);
        $this->patch(route('subscribers.zones.update', [$token, $zone->id]), ['active' => 1]);
        $this->assertTrue($zone->fresh()->active);

        $this->delete(route('subscribers.zones.destroy', [$token, $zone->id]));
        $this->assertModelMissing($zone);

        // No se pueden tocar zonas de otro suscriptor ni entrar con un token falso.
        $other = Subscriber::create(['email' => 'otra@example.com']);
        $otherZone = $other->zones()->create(['label' => 'Casa', 'latitude' => 1.2, 'longitude' => -77.2, 'radius_km' => 5]);
        $this->delete(route('subscribers.zones.destroy', [$token, $otherZone->id]))->assertNotFound();
        $this->get(route('subscribers.manage', 'token-falso'))->assertNotFound();
    }

    public function test_hu11_same_label_reactivates_zone_and_zones_are_limited(): void
    {
        $this->subscribe(['label' => 'Casa', 'radius_km' => 5]);
        $zone = AlertZone::sole();
        $this->get(route('subscribers.unsubscribe', $zone->unsubscribe_token));

        // Volver a suscribir "casa" actualiza y reactiva la misma zona.
        $this->subscribe(['label' => 'casa', 'radius_km' => 8]);
        $this->assertDatabaseCount('alert_zones', 1);
        $this->assertTrue($zone->fresh()->active);
        $this->assertEquals(8, $zone->fresh()->radius_km);

        foreach (['Trabajo', 'Finca', 'Universidad', 'Gimnasio'] as $label) {
            $this->subscribe(['label' => $label])->assertSessionHasNoErrors();
        }
        $this->subscribe(['label' => 'Otra más'])->assertSessionHasErrors('label');
        $this->assertDatabaseCount('alert_zones', config('pets.alerts.max_zones'));
    }

    // ---------- HU-09: compartir un reporte en redes sociales ----------

    public function test_hu09_cp1_share_shows_whatsapp_facebook_x_and_copy_link(): void
    {
        $report = PetReport::factory()->create();

        $this->get(route('reports.show', $report))
            ->assertOk()
            ->assertSee('📣 Compartir', false)
            ->assertSee('data-network="whatsapp"', false)
            ->assertSee('data-network="facebook"', false)
            ->assertSee('data-network="x"', false)
            ->assertSee('Copiar enlace');
    }

    public function test_hu09_cp2_each_network_opens_with_public_link_and_prefilled_text(): void
    {
        $report = PetReport::factory()->perdida()->create(['pet_name' => 'Luna', 'species' => 'perro', 'location_reference' => 'Parque Bolívar']);
        $links = $report->shareLinks();
        $url = rawurlencode(route('reports.show', $report));

        $this->assertStringStartsWith('https://wa.me/?text=', $links['whatsapp']);
        $this->assertStringStartsWith('https://www.facebook.com/sharer/sharer.php?u='.$url, $links['facebook']);
        $this->assertStringStartsWith('https://x.com/intent/post?text=', $links['x']);
        $this->assertStringContainsString($url, $links['whatsapp']);
        $this->assertStringContainsString('&url='.$url, $links['x']);

        $text = rawurldecode(explode('?text=', $links['whatsapp'])[1]);
        $this->assertStringContainsString('Luna (Perro)', $text);
        $this->assertStringContainsString('Parque Bolívar', $text);

        $this->get(route('reports.show', $report))->assertSee(e($links['facebook']), false);
    }

    public function test_hu09_cp3_link_preview_has_photo_name_and_report_type(): void
    {
        $report = PetReport::factory()->encontrada()->create([
            'pet_name' => 'Michi', 'species' => 'gato', 'photo_path' => 'images/seed/gato-naranja.jpg',
        ]);

        $this->get(route('reports.show', $report))
            ->assertSee('<meta property="og:title" content="Mascota encontrada: Michi (Gato)">', false)
            ->assertSee('<meta property="og:image" content="'.asset('images/seed/gato-naranja.jpg').'">', false)
            ->assertSee('<meta property="og:url" content="'.route('reports.show', $report).'">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_hu09_cp4_shared_link_is_never_the_private_management_link(): void
    {
        $report = PetReport::factory()->create();

        foreach ($report->shareLinks() as $link) {
            $this->assertStringNotContainsString($report->management_token, $link);
        }

        // Ni siquiera desde la vista privada de gestión.
        $html = $this->get(route('reports.manage', $report->management_token))->assertOk()->getContent();
        $start = strpos($html, 'data-share-box');
        $shareBox = substr($html, $start, strpos($html, 'id="report-map"') - $start);

        $this->assertStringContainsString(route('reports.show', $report), $shareBox);
        $this->assertStringNotContainsString($report->management_token, $shareBox);
        $this->assertStringContainsString('<meta property="og:url" content="'.route('reports.show', $report).'">', $html);
        $this->assertStringContainsString('noindex', $html);
    }

    // ---------- HU-12: estadísticas de reencuentros ----------

    public function test_hu12_cp1_totals_of_reports_reunited_and_active(): void
    {
        PetReport::factory()->count(3)->create();
        PetReport::factory()->count(2)->create(['status' => 'reunido']);
        PetReport::factory()->create(['status' => 'cerrado']);

        $response = $this->get(route('stats.index'))->assertOk();

        $this->assertSame(['reports' => 6, 'reunited' => 2, 'active' => 3], $response->viewData('totals'));
        $response->assertSeeInOrder(['data-stat="reports"', '6', 'data-stat="reunited"', '2', 'data-stat="active"', '3'], false);
    }

    public function test_hu12_cp2_latest_reunited_are_shown_as_success_stories(): void
    {
        $old = PetReport::factory()->create(['pet_name' => 'Viejito', 'species' => 'perro', 'photo_path' => 'images/seed/beagle.jpg']);
        $old->update(['status' => 'reunido']);
        $this->travel(1)->day();
        $new = PetReport::factory()->create(['pet_name' => 'Reciente', 'species' => 'gato', 'photo_path' => 'images/seed/gato-negro.jpg']);
        $new->update(['status' => 'reunido']);
        PetReport::factory()->create(['pet_name' => 'Activo']);

        $this->assertNotNull($new->fresh()->reunited_at);

        $this->get(route('stats.index'))
            ->assertSee('Historias de éxito')
            ->assertSeeInOrder(['Reciente', 'Gato', 'Viejito', 'Perro'])
            ->assertSee(asset('images/seed/gato-negro.jpg'))
            ->assertDontSee('Activo</h3>', false);
    }

    public function test_hu12_cp3_charts_by_month_and_species(): void
    {
        PetReport::factory()->count(2)->create(['species' => 'perro']);
        PetReport::factory()->create(['species' => 'gato']);
        PetReport::factory()->create(['species' => 'gato', 'created_at' => now()->subMonths(2)]);
        PetReport::factory()->create(['species' => 'ave', 'created_at' => now()->subMonths(13)]);

        $response = $this->get(route('stats.index'))
            ->assertOk()
            ->assertSee('Reportes por mes')
            ->assertSee('Reportes por especie')
            ->assertSee('data-chart="month"', false)
            ->assertSee('data-chart="species"', false);

        $byMonth = $response->viewData('byMonth');
        $this->assertCount(12, $byMonth);
        $this->assertSame(3, end($byMonth)['total']);
        $this->assertSame(1, $byMonth[9]['total']);
        $this->assertSame(4, array_sum(array_column($byMonth, 'total')));

        $this->assertSame(
            [['Gato', 2], ['Perro', 2], ['Ave', 1]],
            collect($response->viewData('bySpecies'))->map(fn ($r) => [$r['label'], $r['total']])->sortBy([[1, 'desc'], [0, 'asc']])->values()->all()
        );
    }

    public function test_hu12_cp4_without_reunited_reports_invites_to_report_and_hides_stories(): void
    {
        PetReport::factory()->count(2)->create();

        $this->get(route('stats.index'))
            ->assertOk()
            ->assertSee('Todavía no hay reencuentros registrados')
            ->assertSee(route('reports.create', 'perdida'))
            ->assertDontSee('Historias de éxito')
            ->assertDontSee('data-story', false);
    }

    public function test_hu12_reunited_date_is_set_when_marking_and_cleared_when_reopened(): void
    {
        $report = PetReport::factory()->create();

        $this->patch(route('reports.updateStatus', $report->management_token), ['status' => 'reunido']);
        $this->assertNotNull($report->fresh()->reunited_at);

        $this->patch(route('reports.updateStatus', $report->management_token), ['status' => 'activo']);
        $this->assertNull($report->fresh()->reunited_at);
    }
}
