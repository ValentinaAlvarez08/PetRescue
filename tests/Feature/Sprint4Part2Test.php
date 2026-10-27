<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PetProfile;
use App\Models\PetReport;
use App\Models\Post;
use App\Notifications\BusinessRequestedNotification;
use App\Notifications\BusinessRequestStatusNotification;
use App\Notifications\ContentHiddenNotification;
use App\Notifications\PossibleMatchNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Casos de prueba del Sprint 4 (segunda parte):
 * HU-05, HU-08, HU-07, HU-20, HU-10, HU-19 y HU-21.
 */
class Sprint4Part2Test extends TestCase
{
    use RefreshDatabase;

    // Puntos en Pasto: el centro, uno a ~1 km y otro a ~30 km.
    private const CENTRO = [1.2136, -77.2811];
    private const CERCA = [1.2226, -77.2811];
    private const LEJOS = [1.4836, -77.2811];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Notification::fake();
        config(['community.moderation.email' => 'moderacion@petrescue.test']);
    }

    private function reportData(array $overrides = []): array
    {
        return array_merge([
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
            'contact_email' => 'familia@example.com',
            'latitude' => self::CENTRO[0],
            'longitude' => self::CENTRO[1],
            'location_reference' => 'Parque Infantil',
        ], $overrides);
    }

    private function publishReport(array $overrides = []): PetReport
    {
        $this->post('/reportar', $this->reportData($overrides))->assertSessionHasNoErrors();

        return PetReport::latest('id')->first();
    }

    /** Reporte guardado directamente (sin disparar avisos). */
    private function makeReport(array $overrides = []): PetReport
    {
        return PetReport::factory()->create(array_merge([
            'type' => 'encontrada',
            'species' => 'perro',
            'breed' => null,
            'sex' => 'hembra',
            'size' => 'grande',
            'color' => 'crema',
            'event_date' => now()->toDateString(),
            'pet_name' => null,
            'contact_phone' => '3109876543',
            'contact_email' => 'rescatista@example.com',
            'latitude' => self::CERCA[0],
            'longitude' => self::CERCA[1],
        ], $overrides));
    }

    // ---------- HU-05: sugerir reportes similares ----------

    public function test_hu05_cp1_report_shows_similar_reports_of_the_opposite_type_with_score_and_reasons(): void
    {
        $lost = $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'pet_name' => 'Luna', 'latitude' => self::CENTRO[0], 'longitude' => self::CENTRO[1]]);
        $match = $this->makeReport(['location_reference' => 'Barrio Las Cuadras']);
        $otherSpecies = $this->makeReport(['species' => 'gato', 'size' => null, 'location_reference' => 'Gato cercano']);
        $far = $this->makeReport(['latitude' => self::LEJOS[0], 'longitude' => self::LEJOS[1], 'location_reference' => 'Muy lejos']);
        $closed = $this->makeReport(['status' => 'cerrado', 'location_reference' => 'Ya cerrado']);
        $sameType = $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'location_reference' => 'Otra perdida']);
        $differentColor = $this->makeReport(['color' => 'negro', 'sex' => 'macho', 'size' => 'pequeño', 'location_reference' => 'Otro color']);

        $similar = $lost->similarReports();
        $this->assertSame([$match->id], $similar->pluck('id')->all());
        $this->assertGreaterThanOrEqual(config('pets.matching.min_score'), $similar->first()->match_score);
        $this->assertContains('Mismo color: Crema / dorado', $similar->first()->match_reasons);

        $this->get(route('reports.show', $lost))
            ->assertOk()
            ->assertSee('Mascotas encontradas que se parecen')
            ->assertSee(route('reports.show', $match), false)
            ->assertSee('% parecido')
            ->assertDontSee('Gato cercano')
            ->assertDontSee('Muy lejos')
            ->assertDontSee('Ya cerrado')
            ->assertDontSee('Otra perdida')
            ->assertDontSee('Otro color');

        // Y al revés: desde la encontrada se sugiere la perdida.
        $this->get(route('reports.show', $match))->assertSee('Mascotas perdidas que se parecen')->assertSee(route('reports.show', $lost), false);
    }

    public function test_hu05_cp2_suggestions_before_publishing_come_from_the_api(): void
    {
        $lost = $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'pet_name' => 'Luna']);

        $this->getJson(route('reports.similar', [
            'type' => 'encontrada', 'species' => 'perro', 'latitude' => self::CENTRO[0], 'longitude' => self::CENTRO[1],
            'colors' => ['crema'], 'sex' => 'hembra', 'size' => 'grande', 'breed' => config('pets.unknown_breed'),
            'event_date' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('reports.0.id', $lost->id)
            ->assertJsonPath('reports.0.url', route('reports.show', $lost))
            ->assertJsonMissing(['management_token' => $lost->management_token]);

        $this->getJson(route('reports.similar', ['type' => 'encontrada']))->assertStatus(422);

        // El formulario consulta la API en el último paso.
        $this->get(route('reports.create', 'encontrada'))->assertSee('id="similar-box"', false)->assertSee(json_encode(route('reports.similar')), false);
    }

    public function test_hu05_cp3_the_owner_sees_possible_matches_without_exposing_private_links(): void
    {
        $lost = $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'pet_name' => 'Luna']);
        $match = $this->makeReport();

        $this->get(route('reports.manage', $lost->management_token))
            ->assertOk()
            ->assertSee('Posibles coincidencias con tu reporte')
            ->assertSee(route('reports.show', $match), false)
            ->assertDontSee($match->management_token);

        // Sin coincidencias, el dueño ve que se le avisará por correo.
        $alone = $this->makeReport(['type' => 'perdida', 'species' => 'ave', 'size' => null, 'breed' => 'Canario']);
        $this->get(route('reports.manage', $alone->management_token))->assertSee('Por ahora no hay reportes parecidos');
    }

    public function test_hu05_cp4_incompatible_dates_are_not_suggested(): void
    {
        $lost = $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'event_date' => now()->subDays(5)->toDateString()]);
        $foundBefore = $this->makeReport(['event_date' => now()->subDays(20)->toDateString()]);
        $foundAfter = $this->makeReport(['event_date' => now()->subDays(2)->toDateString()]);

        $this->assertSame([$foundAfter->id], $lost->similarReports()->pluck('id')->all());
        $this->assertNotContains($foundBefore->id, $lost->similarReports()->pluck('id'));
    }

    // ---------- HU-08: aviso de posible coincidencia ----------

    public function test_hu08_cp1_owner_of_a_matching_report_receives_an_email(): void
    {
        $lost = $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'pet_name' => 'Luna', 'contact_email' => 'duena@example.com', 'contact_phone' => '3111111111']);

        $found = $this->publishReport([
            'type' => 'encontrada', 'breed' => config('pets.unknown_breed'), 'pet_name' => null,
            'contact_email' => null, 'latitude' => self::CERCA[0], 'longitude' => self::CERCA[1],
        ]);

        Notification::assertSentOnDemand(PossibleMatchNotification::class, function ($notification, $channels, $notifiable) use ($lost, $found) {
            $mail = $notification->toMail($notifiable);

            return $notifiable->routes['mail'] === 'duena@example.com'
                && $notification->ownReport->is($lost)
                && $mail->actionUrl === route('reports.show', $found)
                && str_contains(implode(' ', $mail->introLines), 'Mismo color')
                && str_contains(implode(' ', $mail->outroLines), route('reports.manage', $lost->management_token));
        });

        $this->assertDatabaseHas('report_matches', ['lost_report_id' => $lost->id, 'found_report_id' => $found->id]);
    }

    public function test_hu08_cp2_no_email_without_contact_email_or_without_match(): void
    {
        $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'contact_email' => null, 'contact_phone' => '3111111111']);
        $this->makeReport(['type' => 'perdida', 'species' => 'gato', 'size' => null, 'breed' => 'Siamés', 'contact_email' => 'gato@example.com', 'contact_phone' => '3122222222']);

        $this->publishReport(['type' => 'encontrada', 'breed' => null, 'latitude' => self::CERCA[0], 'longitude' => self::CERCA[1]]);

        Notification::assertSentOnDemandTimes(PossibleMatchNotification::class, 0);
    }

    public function test_hu08_cp3_each_match_is_notified_once_and_editing_can_find_new_ones(): void
    {
        $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'contact_email' => 'duena@example.com', 'contact_phone' => '3111111111']);
        $found = $this->publishReport(['type' => 'encontrada', 'breed' => null, 'latitude' => self::CERCA[0], 'longitude' => self::CERCA[1]]);
        Notification::assertSentOnDemandTimes(PossibleMatchNotification::class, 1);

        // Editar sin cambios relevantes no repite el aviso.
        $this->patch(route('reports.update', $found->management_token), $this->reportData([
            'type' => 'encontrada', 'breed' => null, 'photo' => null, 'description' => 'Lo encontré cerca del parque, está bien.',
            'latitude' => self::CERCA[0], 'longitude' => self::CERCA[1],
        ]))->assertSessionHasNoErrors();
        Notification::assertSentOnDemandTimes(PossibleMatchNotification::class, 1);

        // Un perro negro perdido no coincide... hasta que quien la encontró corrige el color.
        $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'color' => 'negro', 'contact_email' => 'otra@example.com', 'contact_phone' => '3133333333']);
        $this->patch(route('reports.update', $found->management_token), $this->reportData([
            'type' => 'encontrada', 'breed' => null, 'photo' => null, 'colors' => ['crema', 'negro'],
            'latitude' => self::CERCA[0], 'longitude' => self::CERCA[1],
        ]))->assertSessionHasNoErrors();

        Notification::assertSentOnDemand(PossibleMatchNotification::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === 'otra@example.com');
        Notification::assertSentOnDemandTimes(PossibleMatchNotification::class, 2);
    }

    public function test_hu08_cp4_no_email_when_both_reports_belong_to_the_same_person(): void
    {
        $this->makeReport(['type' => 'perdida', 'breed' => 'Labrador', 'contact_email' => 'yo@example.com', 'contact_phone' => '3001234567']);
        $this->publishReport(['type' => 'encontrada', 'breed' => null, 'contact_phone' => '300 123 4567']);

        Notification::assertSentOnDemandTimes(PossibleMatchNotification::class, 0);
    }

    // ---------- HU-07: editar o cerrar un reporte con el enlace privado ----------

    public function test_hu07_cp1_owner_edits_the_report_with_the_private_link(): void
    {
        $report = $this->publishReport();
        $oldPhoto = $report->photo_path;

        $this->get(route('reports.manage', $report->management_token))->assertSee(route('reports.edit', $report->management_token), false);
        $this->get(route('reports.show', $report))->assertDontSee(route('reports.edit', $report->management_token), false);

        $this->get(route('reports.edit', $report->management_token))
            ->assertOk()
            ->assertSee('Editar el reporte')
            ->assertSee('value="Luna"', false)
            ->assertSee('Parque Infantil');

        // Sin foto nueva se conserva la actual; el tipo y la especie no cambian aunque se manipule el formulario.
        $this->patch(route('reports.update', $report->management_token), $this->reportData([
            'type' => 'encontrada', 'species' => 'gato', 'photo' => null,
            'description' => 'Ahora tiene un collar azul y está un poco más delgada.',
            'contact_phone' => '3209998877', 'location_reference' => 'Barrio San Ignacio', 'colors' => ['crema', 'blanco'],
        ]))->assertRedirect(route('reports.manage', $report->management_token))->assertSessionHasNoErrors();

        $report->refresh();
        $this->assertSame('perdida', $report->type);
        $this->assertSame('perro', $report->species);
        $this->assertSame('3209998877', $report->contact_phone);
        $this->assertSame('crema,blanco', $report->color);
        $this->assertSame('Barrio San Ignacio', $report->location_reference);
        $this->assertSame($oldPhoto, $report->photo_path);

        // Con foto nueva se reemplaza y se borra la anterior.
        $this->patch(route('reports.update', $report->management_token), $this->reportData(['photo' => UploadedFile::fake()->image('nueva.jpg')]));
        $this->assertNotSame($oldPhoto, $report->fresh()->photo_path);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($report->fresh()->photo_path);
    }

    public function test_hu07_cp2_edit_uses_the_same_validations(): void
    {
        $report = $this->publishReport();

        $this->patch(route('reports.update', $report->management_token), $this->reportData([
            'photo' => null, 'description' => 'corta', 'contact_phone' => '12345', 'colors' => [], 'event_date' => now()->addDay()->toDateString(),
        ]))->assertSessionHasErrors(['description', 'contact_phone', 'colors', 'event_date']);

        $this->assertSame('Se perdió cerca del parque, tiene collar rojo.', $report->fresh()->description);
    }

    public function test_hu07_cp3_owner_closes_and_reopens_the_report(): void
    {
        $report = $this->publishReport();

        $this->patch(route('reports.updateStatus', $report->management_token), ['status' => 'cerrado'])
            ->assertRedirect(route('reports.manage', $report->management_token))
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'Cerraste el reporte'));
        $this->assertSame('cerrado', $report->fresh()->status);
        $this->get(route('reports.index'))->assertDontSee(route('reports.show', $report), false);

        // Cerrado: no se puede editar hasta reabrirlo.
        $this->get(route('reports.edit', $report->management_token))->assertRedirect(route('reports.manage', $report->management_token));
        $this->patch(route('reports.update', $report->management_token), $this->reportData(['photo' => null, 'pet_name' => 'Otra']));
        $this->assertSame('Luna', $report->fresh()->pet_name);
        $this->get(route('reports.manage', $report->management_token))->assertSee('Reabrir reporte')->assertDontSee('Marcar como reunido');

        $this->patch(route('reports.updateStatus', $report->management_token), ['status' => 'activo']);
        $this->assertSame('activo', $report->fresh()->status);
        $this->get(route('reports.index'))->assertSee(route('reports.show', $report), false);
    }

    public function test_hu07_cp4_without_the_token_nothing_can_be_edited(): void
    {
        $report = $this->publishReport();

        $this->get('/reporte/token-inventado/editar')->assertNotFound();
        $this->patch('/reporte/token-inventado', $this->reportData(['photo' => null]))->assertNotFound();
        $this->patch('/reporte/token-inventado/estado', ['status' => 'cerrado'])->assertNotFound();
        $this->assertSame('activo', $report->fresh()->status);
    }

    // ---------- HU-20: reportar contenido inapropiado ----------

    private function flagAs(string $ip, string $url, array $data = ['reason' => 'spam'])
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])->post($url, $data);
    }

    public function test_hu20_cp1_report_post_and_comment_can_be_flagged_with_a_reason(): void
    {
        $report = $this->makeReport();
        $post = Post::factory()->create(['topic' => 'salud']);
        $comment = $post->comments()->create(['nickname' => 'Ana', 'body' => 'Compra seguidores aquí']);

        $this->get(route('reports.show', $report))->assertSee(route('flags.report', $report), false)->assertSee('Spam o publicidad');
        $this->get(route('posts.show', ['salud', $post]))
            ->assertSee(route('flags.post', ['salud', $post]), false)
            ->assertSee(route('flags.comment', $comment), false);

        $this->flagAs('10.0.0.1', route('flags.report', $report), ['reason' => 'falso', 'details' => 'Pide plata por adelantado'])
            ->assertRedirect(route('reports.show', $report))
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'Gracias por avisar'));
        $this->flagAs('10.0.0.1', route('flags.post', ['salud', $post]), ['reason' => 'ofensivo'])->assertSessionHasNoErrors();
        $this->flagAs('10.0.0.1', route('flags.comment', $comment), ['reason' => 'spam'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('content_flags', ['flaggable_type' => 'reporte', 'flaggable_id' => $report->id, 'reason' => 'falso', 'details' => 'Pide plata por adelantado']);
        $this->assertDatabaseHas('content_flags', ['flaggable_type' => 'publicacion', 'flaggable_id' => $post->id, 'reason' => 'ofensivo']);
        $this->assertDatabaseHas('content_flags', ['flaggable_type' => 'comentario', 'flaggable_id' => $comment->id, 'reason' => 'spam']);

        // El motivo es obligatorio, y "Otro motivo" pide detalle.
        $this->flagAs('10.0.0.2', route('flags.report', $report), [])->assertSessionHasErrorsIn('flag', 'reason');
        $this->flagAs('10.0.0.2', route('flags.report', $report), ['reason' => 'otro'])->assertSessionHasErrorsIn('flag', 'details');
        $this->flagAs('10.0.0.2', route('flags.report', $report), ['reason' => 'inventado'])->assertSessionHasErrorsIn('flag', 'reason');
        $this->assertDatabaseCount('content_flags', 3);
    }

    public function test_hu20_cp2_the_same_person_counts_once(): void
    {
        $report = $this->makeReport();

        $this->flagAs('10.0.0.1', route('flags.report', $report));
        $this->flagAs('10.0.0.1', route('flags.report', $report))
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'Ya habías denunciado'));
        $this->flagAs('10.0.0.1', route('flags.report', $report));

        $this->assertSame(1, $report->flags()->count());
        $this->assertNull($report->fresh()->hidden_at);
        // No se guarda la IP, solo un hash.
        $this->assertDatabaseMissing('content_flags', ['reporter_hash' => '10.0.0.1']);
    }

    public function test_hu20_cp3_content_is_hidden_after_the_threshold_and_moderation_is_notified(): void
    {
        $report = $this->makeReport(['location_reference' => 'Reporte falso']);
        $post = Post::factory()->create(['topic' => 'salud', 'title' => 'Vendo cachorros baratos']);
        $comment = $post->comments()->create(['nickname' => 'Spammer', 'body' => 'Visita mi página de apuestas']);

        foreach (['10.0.0.1', '10.0.0.2', '10.0.0.3'] as $ip) {
            $this->flagAs($ip, route('flags.report', $report));
            $this->flagAs($ip, route('flags.comment', $comment));
        }
        $this->flagAs('10.0.0.1', route('flags.post', ['salud', $post]), ['reason' => 'venta']);
        $this->flagAs('10.0.0.2', route('flags.post', ['salud', $post]), ['reason' => 'venta']);
        $this->flagAs('10.0.0.3', route('flags.post', ['salud', $post]), ['reason' => 'venta'])
            ->assertRedirect(route('community.show', 'salud'))
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'quedó oculto'));

        $this->assertNotNull($report->fresh()->hidden_at);
        $this->get(route('reports.show', $report))->assertNotFound();
        $this->get(route('reports.index'))->assertDontSee('Reporte falso');
        $this->get(route('reports.manage', $report->management_token))->assertOk()->assertSee('Tu reporte está oculto');

        $this->get(route('posts.show', ['salud', $post]))->assertNotFound();
        $this->get(route('community.show', 'salud'))->assertDontSee('Vendo cachorros baratos');
        $this->get(route('posts.manage', $post->management_token))->assertSee('Tu publicación está oculta');

        $other = Post::factory()->create(['topic' => 'salud']);
        $comment->update(['post_id' => $other->id]);
        $this->get(route('posts.show', ['salud', $other]))->assertOk()->assertDontSee('Visita mi página de apuestas');

        // Un aviso a moderación por cada contenido, solo cuando se oculta.
        Notification::assertSentOnDemandTimes(ContentHiddenNotification::class, 3);
        Notification::assertSentOnDemand(ContentHiddenNotification::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === 'moderacion@petrescue.test');
    }

    public function test_hu20_cp4_moderation_restores_content_only_with_the_signed_link(): void
    {
        $report = $this->makeReport();
        foreach (['10.0.0.1', '10.0.0.2', '10.0.0.3'] as $ip) {
            $this->flagAs($ip, route('flags.report', $report), ['reason' => 'falso']);
        }
        $this->assertTrue($report->fresh()->isHidden());

        $this->get("/moderacion/contenido/reporte/{$report->id}")->assertForbidden();
        $this->post("/moderacion/contenido/reporte/{$report->id}")->assertForbidden();

        $url = ContentHiddenNotification::reviewUrl($report);
        $this->get($url)->assertOk()->assertSee('Información falsa o posible estafa')->assertSee('Volver a mostrar');
        $this->post($url)->assertRedirect($url);

        $this->assertFalse($report->fresh()->isHidden());
        $this->assertSame(0, $report->flags()->count());
        $this->get(route('reports.show', $report))->assertOk();
    }

    // ---------- HU-10: perfil digital de la mascota ----------

    private function profileData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tobby',
            'species' => 'perro',
            'breed' => 'Beagle',
            'sex' => 'macho',
            'size' => 'mediano',
            'colors' => ['cafe', 'blanco'],
            'birth_date' => now()->subYears(3)->toDateString(),
            'photo' => UploadedFile::fake()->image('tobby.jpg'),
            'distinctive_marks' => 'Tiene una mancha negra en el lomo y la oreja derecha caída.',
            'sterilized' => 'si',
            'vaccines' => 'Antirrábica marzo 2026',
            'health_notes' => 'Es alérgico al pollo.',
            'owner_name' => 'Valentina',
            'contact_phone' => '300 555 1234',
            'contact_whatsapp' => '1',
            'contact_email' => 'vale@example.com',
            'neighborhood' => 'San Ignacio',
        ], $overrides);
    }

    private function createProfile(array $overrides = []): PetProfile
    {
        $this->post(route('profiles.store'), $this->profileData($overrides))->assertSessionHasNoErrors();

        return PetProfile::latest('id')->first();
    }

    public function test_hu10_cp1_owner_creates_the_profile_and_receives_a_private_link(): void
    {
        $this->get(route('profiles.create'))->assertOk()->assertSee('name="sterilized"', false)->assertSee('name="health_notes"', false);

        $response = $this->post(route('profiles.store'), $this->profileData());
        $profile = PetProfile::sole();

        $response->assertRedirect(route('profiles.manage', $profile->management_token));
        $this->assertSame('3005551234', $profile->contact_phone);
        $this->assertSame('cafe,blanco', $profile->color);
        $this->assertSame(8, strlen($profile->public_code));
        Storage::disk('public')->assertExists($profile->photo_path);

        // Campos obligatorios y catálogos.
        $this->post(route('profiles.store'), $this->profileData([
            'name' => '', 'photo' => null, 'species' => 'tigre', 'contact_phone' => '123', 'birth_date' => now()->addDay()->toDateString(),
        ]))->assertSessionHasErrors(['name', 'photo', 'species', 'contact_phone', 'birth_date']);
        $this->assertDatabaseCount('pet_profiles', 1);
    }

    public function test_hu10_cp2_public_profile_shows_identity_health_and_contact_but_not_the_private_link(): void
    {
        $profile = $this->createProfile();

        $this->get(route('profiles.show', $profile))
            ->assertOk()
            ->assertSee('Tobby')
            ->assertSee('Beagle')
            ->assertSee('3 años')
            ->assertSee('Es alérgico al pollo.')
            ->assertSee('https://wa.me/573005551234', false)
            ->assertDontSee($profile->management_token);

        // El enlace público usa el código corto, no el id.
        $this->get('/mascota/'.$profile->id)->assertNotFound();
    }

    public function test_hu10_cp3_private_link_allows_editing_printing_the_qr_and_deleting(): void
    {
        $profile = $this->createProfile();

        $this->get(route('profiles.manage', $profile->management_token))
            ->assertOk()
            ->assertSee('data-url="'.$profile->public_url.'"', false)
            ->assertSee('Imprimir placa');

        $this->patch(route('profiles.update', $profile->management_token), $this->profileData(['photo' => null, 'health_notes' => 'Ya no es alérgico.']))
            ->assertRedirect(route('profiles.manage', $profile->management_token));
        $this->assertSame('Ya no es alérgico.', $profile->fresh()->health_notes);

        $this->patch('/mi-mascota/'.fake()->uuid(), $this->profileData())->assertNotFound();

        $this->delete(route('profiles.destroy', $profile->management_token))->assertRedirect(route('profiles.create'));
        $this->assertDatabaseCount('pet_profiles', 0);
        Storage::disk('public')->assertMissing($profile->photo_path);
        $this->get(route('profiles.show', $profile))->assertNotFound();
    }

    public function test_hu10_cp4_lost_report_is_created_from_the_profile(): void
    {
        $profile = $this->createProfile();
        $createUrl = route('reports.create', ['type' => 'perdida', 'perfil' => $profile->management_token]);

        $this->get(route('profiles.manage', $profile->management_token))->assertSee($createUrl, false);
        $this->get($createUrl)
            ->assertOk()
            ->assertSee('Usamos los datos del perfil de Tobby')
            ->assertSee('value="Tobby"', false)
            ->assertSee('Tiene una mancha negra en el lomo')
            ->assertSee('name="pet_profile_token" value="'.$profile->management_token.'"', false);

        // Se publica sin subir foto: se usa una copia de la del perfil.
        $report = $this->publishReport([
            'pet_profile_token' => $profile->management_token, 'photo' => null, 'pet_name' => 'Tobby',
            'breed' => 'Beagle', 'sex' => 'macho', 'size' => 'mediano', 'colors' => ['cafe', 'blanco'],
        ]);
        $this->assertSame($profile->id, $report->pet_profile_id);
        $this->assertNotSame($profile->photo_path, $report->photo_path);
        Storage::disk('public')->assertExists($report->photo_path);

        // El perfil público avisa que está perdida.
        $this->get(route('profiles.show', $profile))->assertSee('está perdida')->assertSee(route('reports.show', $report), false);

        // Sin perfil, la foto sigue siendo obligatoria.
        $this->post('/reportar', $this->reportData(['photo' => null]))->assertSessionHasErrors('photo');
    }

    // ---------- HU-19: solicitar registro de negocio en el directorio ----------

    private function businessData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Veterinaria Patitas Felices',
            'category' => 'veterinaria',
            'description' => 'Consulta general, vacunación y cirugías para perros y gatos.',
            'services' => 'Vacunas, Cirugía, Urgencias',
            'phone' => '602 723 4567',
            'has_whatsapp' => '0',
            'email' => 'contacto@patitas.test',
            'address' => 'Calle 18 # 25-30, Pasto',
            'schedule' => 'Lun a sáb 8:00 a. m. – 6:00 p. m.',
            'latitude' => self::CENTRO[0],
            'longitude' => self::CENTRO[1],
            'requester_name' => 'Carlos',
            'requester_email' => 'carlos@patitas.test',
            'authorized' => '1',
        ], $overrides);
    }

    public function test_hu19_cp1_request_is_saved_as_pending_and_both_parties_are_notified(): void
    {
        $this->get(route('directory.index'))->assertSee(route('directory.create'), false);
        $this->get(route('directory.create'))->assertOk()->assertSee('name="requester_email"', false)->assertSee('data-role="map"', false);

        $this->post(route('directory.store'), $this->businessData())
            ->assertRedirect(route('directory.index'))
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'Recibimos la solicitud'));

        $business = Business::sole();
        $this->assertSame('pendiente', $business->review_status);
        $this->assertFalse($business->is_active);
        $this->assertSame('6027234567', $business->phone);
        $this->get(route('directory.index'))->assertViewHas('businesses', fn ($businesses) => $businesses->isEmpty());

        Notification::assertSentOnDemand(BusinessRequestStatusNotification::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === 'carlos@patitas.test');
        Notification::assertSentOnDemand(BusinessRequestedNotification::class, function ($notification, $c, $notifiable) {
            return $notifiable->routes['mail'] === 'moderacion@petrescue.test'
                && str_contains($notification->toMail($notifiable)->actionUrl, 'signature=');
        });
    }

    public function test_hu19_cp2_request_is_validated(): void
    {
        $this->post(route('directory.store'), $this->businessData([
            'authorized' => null, 'phone' => '12345', 'latitude' => null, 'category' => 'casino',
            'requester_email' => 'no-es-correo', 'description' => 'corta',
        ]))->assertSessionHasErrors(['authorized', 'phone', 'latitude', 'category', 'requester_email', 'description']);

        $this->assertDatabaseCount('businesses', 0);
    }

    public function test_hu19_cp3_moderation_approves_or_rejects_with_the_signed_link(): void
    {
        $this->post(route('directory.store'), $this->businessData());
        $this->post(route('directory.store'), $this->businessData(['name' => 'Tienda Dudosa', 'category' => 'tienda']));
        [$vet, $shop] = Business::orderBy('id')->get()->all();

        $this->get(route('moderation.businesses.show', $vet))->assertForbidden();

        $vetUrl = BusinessRequestedNotification::reviewUrl($vet);
        $this->get($vetUrl)->assertOk()->assertSee('Veterinaria Patitas Felices')->assertSee('Aprobar');
        $this->post($vetUrl, ['decision' => 'aprobar'])->assertRedirect($vetUrl);

        $this->assertTrue($vet->fresh()->is_active);
        $this->assertSame('aprobado', $vet->fresh()->review_status);
        $this->get(route('directory.index'))->assertSee('Veterinaria Patitas Felices');

        $shopUrl = BusinessRequestedNotification::reviewUrl($shop);
        $this->post($shopUrl, ['decision' => 'rechazar', 'note' => 'No pudimos verificar la dirección.']);
        $this->assertSame('rechazado', $shop->fresh()->review_status);
        $this->assertFalse($shop->fresh()->is_active);

        Notification::assertSentOnDemand(BusinessRequestStatusNotification::class, function ($notification, $c, $notifiable) {
            $mail = $notification->toMail($notifiable);

            return $notification->business->review_status === 'rechazado'
                && str_contains(implode(' ', $mail->introLines), 'No pudimos verificar la dirección.');
        });

        // Una solicitud revisada no se vuelve a cambiar.
        $this->post($shopUrl, ['decision' => 'aprobar']);
        $this->assertSame('rechazado', $shop->fresh()->review_status);
    }

    public function test_hu19_cp4_a_business_already_listed_or_pending_cannot_be_requested_again(): void
    {
        $this->post(route('directory.store'), $this->businessData());
        $this->post(route('directory.store'), $this->businessData(['name' => '  veterinaria patitas felices ']))->assertSessionHasErrors('name');
        $this->assertDatabaseCount('businesses', 1);

        // Si la rechazaron, puede volver a intentarlo con los datos corregidos.
        Business::sole()->review(false);
        $this->post(route('directory.store'), $this->businessData())->assertSessionHasNoErrors();
        $this->assertDatabaseCount('businesses', 2);
    }

    // ---------- HU-21: guías rápidas por tema ----------

    public function test_hu21_cp1_guides_are_listed_and_filtered_by_topic(): void
    {
        $guides = config('guides.guides');

        $response = $this->get(route('guides.index'))->assertOk();
        foreach (config('guides.topics') as $key => $topic) {
            $response->assertSee('data-topic="'.$key.'"', false)->assertSee($topic['label']);
        }
        foreach ($guides as $guide) {
            $response->assertSee($guide['title']);
        }

        $this->get(route('guides.index', ['tema' => 'emergencias']))
            ->assertSee('Golpe de calor: cómo reconocerlo y qué hacer')
            ->assertDontSee('Alimentos y productos que nunca debes darles');

        // Un tema inventado muestra todas.
        $this->get(route('guides.index', ['tema' => 'inventado']))->assertOk()->assertSee($guides['alimentos-prohibidos']['title']);
    }

    public function test_hu21_cp2_guide_shows_numbered_steps_warning_and_related_links(): void
    {
        $guide = config('guides.guides.golpe-de-calor');

        $response = $this->get(route('guides.show', 'golpe-de-calor'))
            ->assertOk()
            ->assertSee($guide['title'])
            ->assertSee($guide['warning'])
            ->assertSee(route('directory.index', ['categoria' => 'veterinaria']), false)
            ->assertSee(route('community.show', 'salud'), false)
            ->assertSee(route('guides.show', 'intoxicacion'), false);

        $this->assertSame(count($guide['steps']), substr_count($response->getContent(), 'data-role="guide-step"'));

        $this->get('/guias/no-existe')->assertNotFound();
        $this->get('/guias/golpe-de-calor.steps')->assertNotFound();
    }

    public function test_hu21_cp3_guides_are_linked_from_navigation_and_report_forms(): void
    {
        $this->get(route('home'))->assertSee(route('guides.index'), false)->assertSee(route('profiles.create'), false);
        $this->get(route('reports.create', 'perdida'))->assertSee(route('guides.show', 'perdi-mi-mascota'), false);
        $this->get(route('reports.create', 'encontrada'))->assertSee(route('guides.show', 'encontre-una-mascota'), false);
    }
}
