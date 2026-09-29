<?php

namespace Tests\Feature;

use App\Events\PetReportPublished;
use App\Models\Business;
use App\Models\PetReport;
use Database\Seeders\BusinessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Casos de prueba del Sprint 2: HU-13, HU-14, HU-15, HU-16 y HU-06.
 */
class Sprint2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Event::fake([PetReportPublished::class]);
    }

    private function validReport(array $overrides = []): array
    {
        return array_merge([
            'type' => 'perdida',
            'species' => 'gato',
            'breed' => 'Siamés',
            'pet_name' => 'Luna',
            'sex' => 'hembra',
            'colors' => ['crema', 'cafe'],
            'event_date' => now()->subDay()->toDateString(),
            'description' => 'Collar rosado con cascabel, ojos azules, muy asustadiza.',
            'photo' => UploadedFile::fake()->image('luna.jpg'),
            'contact_phone' => '300 123 4567',
            'contact_whatsapp' => '1',
            'latitude' => 1.2136,
            'longitude' => -77.2811,
            'location_reference' => 'Parque Infantil',
        ], $overrides);
    }

    // ---------- HU-13: formulario guiado con especies limitadas ----------

    public function test_hu13_cp1_valid_report_is_saved_and_redirects_to_private_link(): void
    {
        $response = $this->post('/reportar', $this->validReport());

        $report = PetReport::first();
        $this->assertNotNull($report);
        $this->assertSame('gato', $report->species);
        $this->assertSame('crema,cafe', $report->color);
        $this->assertSame('3001234567', $report->contact_phone);
        $response->assertRedirect(route('reports.manage', $report->management_token));
    }

    public function test_hu13_cp2_non_domestic_species_is_rejected(): void
    {
        $this->post('/reportar', $this->validReport(['species' => 'mesa']))
            ->assertSessionHasErrors('species');

        $this->assertDatabaseCount('pet_reports', 0);
    }

    public function test_hu13_cp3_breed_must_belong_to_species(): void
    {
        $this->post('/reportar', $this->validReport(['species' => 'gato', 'breed' => 'Labrador']))
            ->assertSessionHasErrors('breed');
    }

    public function test_hu13_cp4_dog_requires_size_and_max_three_colors(): void
    {
        $this->post('/reportar', $this->validReport([
            'species' => 'perro', 'breed' => 'Labrador', 'size' => null,
            'colors' => ['negro', 'blanco', 'cafe', 'gris'],
        ]))->assertSessionHasErrors(['size', 'colors']);
    }

    public function test_hu13_cp5_future_date_and_invalid_phone_are_rejected(): void
    {
        $this->post('/reportar', $this->validReport([
            'event_date' => now()->addDay()->toDateString(),
            'contact_phone' => '12345',
        ]))->assertSessionHasErrors(['event_date', 'contact_phone']);
    }

    public function test_hu13_cp6_finder_can_leave_breed_unknown(): void
    {
        $this->post('/reportar', $this->validReport([
            'type' => 'encontrada', 'breed' => config('pets.unknown_breed'), 'pet_name' => null,
        ]))->assertSessionHasNoErrors();

        $this->assertNull(PetReport::first()->breed);
    }

    // ---------- HU-14: ubicación marcada en el mapa ----------

    public function test_hu14_cp1_location_is_required(): void
    {
        $this->post('/reportar', $this->validReport(['latitude' => null, 'longitude' => null]))
            ->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_hu14_cp2_location_outside_colombia_is_rejected(): void
    {
        // Madrid, España
        $this->post('/reportar', $this->validReport(['latitude' => 40.4168, 'longitude' => -3.7038]))
            ->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_hu14_cp3_form_includes_map_picker(): void
    {
        $this->get('/reportar/perdida')
            ->assertOk()
            ->assertSee('data-role="map"', false)
            ->assertSee('Usar mi ubicación');
    }

    // ---------- HU-15: página principal de la comunidad ----------

    public function test_hu15_cp1_home_shows_welcome_directory_and_community(): void
    {
        $this->seed(BusinessSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('Bienvenido')
            ->assertSee('Mascotas rescatadas')
            ->assertSee('Mascotas reactivas')
            ->assertSee('Tiendas')
            ->assertSee('Guarderías');
    }

    public function test_hu15_cp2_community_topic_page_exists_and_unknown_topic_is_404(): void
    {
        $this->get('/comunidad/rescatadas')->assertOk()->assertSee('Mascotas rescatadas');
        $this->get('/comunidad/no-existe')->assertNotFound();
    }

    // ---------- HU-16: directorio de servicios con mapa ----------

    public function test_hu16_cp1_directory_lists_businesses_with_contact_for_map(): void
    {
        $this->seed(BusinessSeeder::class);
        $business = Business::where('category', 'peluqueria')->first();

        $this->get('/directorio?categoria=peluqueria')
            ->assertOk()
            ->assertSee($business->name)
            ->assertSee($business->phone)
            ->assertSee('dir-map', false);
    }

    public function test_hu16_cp2_inactive_businesses_are_hidden(): void
    {
        Business::create([
            'name' => 'Tienda Cerrada', 'category' => 'tienda', 'description' => 'x',
            'address' => 'x', 'latitude' => 1.21, 'longitude' => -77.28, 'is_active' => false,
        ]);

        $this->get('/directorio')->assertOk()->assertDontSee('Tienda Cerrada');
    }

    // ---------- HU-06: contacto directo y enlace público vs privado ----------

    public function test_hu06_cp1_public_page_shows_whatsapp_but_not_management_actions(): void
    {
        $report = PetReport::factory()->create(['contact_phone' => '3001234567', 'contact_whatsapp' => true]);

        $this->get(route('reports.show', $report))
            ->assertOk()
            ->assertSee('https://wa.me/573001234567', false)
            ->assertDontSee('Marcar como reunido')
            ->assertDontSee($report->management_token);
    }

    public function test_hu06_cp2_private_link_allows_marking_as_reunited(): void
    {
        $report = PetReport::factory()->create();

        $this->get(route('reports.manage', $report->management_token))
            ->assertOk()
            ->assertSee('Marcar como reunido');

        $this->patch(route('reports.updateStatus', $report->management_token), ['status' => 'reunido'])
            ->assertRedirect();

        $this->assertSame('reunido', $report->fresh()->status);
    }

    public function test_hu06_cp3_report_list_never_links_to_management_token(): void
    {
        $report = PetReport::factory()->create();

        $this->get('/reportes')
            ->assertOk()
            ->assertDontSee($report->management_token);
    }
}
