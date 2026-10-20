<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\PetReport;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Casos de prueba del Sprint 3: HU-17, HU-18 y HU-04.
 */
class Sprint3Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function validPost(array $overrides = []): array
    {
        return array_merge([
            'nickname' => 'Mamá de Canela',
            'title' => 'Mi perra rescatada no sale de debajo de la cama',
            'body' => 'La adoptamos hace una semana y todavía tiembla cuando nos acercamos. ¿Qué les funcionó?',
            'photo' => UploadedFile::fake()->image('canela.jpg'),
        ], $overrides);
    }

    // ---------- HU-17: publicar en los espacios de la comunidad ----------

    public function test_hu17_cp1_new_post_button_shows_form_and_limits_are_validated(): void
    {
        $this->get('/comunidad/rescatadas')
            ->assertOk()
            ->assertSee('Nueva publicación')
            ->assertSee(route('posts.create', 'rescatadas'), false);

        $this->get(route('posts.create', 'rescatadas'))
            ->assertOk()
            ->assertSee('name="nickname"', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="body"', false)
            ->assertSee('name="photo"', false);

        // Título de más de 120, texto de menos de 20 y foto que no es jpg/png/webp o pesa más de 5 MB.
        $this->post(route('posts.store', 'rescatadas'), $this->validPost([
            'title' => str_repeat('a', 121),
            'body' => 'Muy corto',
            'photo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasErrors(['title', 'body', 'photo']);

        $this->post(route('posts.store', 'rescatadas'), $this->validPost([
            'body' => str_repeat('a', 2001),
            'photo' => UploadedFile::fake()->image('grande.jpg')->size(6000),
        ]))->assertSessionHasErrors(['body', 'photo']);

        $this->assertDatabaseCount('posts', 0);

        // La foto es opcional.
        $this->post(route('posts.store', 'rescatadas'), $this->validPost(['photo' => null]))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('posts', 1);
    }

    public function test_hu17_cp2_valid_post_appears_first_and_private_link_allows_edit_and_delete(): void
    {
        Post::factory()->inTopic('rescatadas')->create([
            'title' => 'Publicación antigua', 'created_at' => now()->subDay(),
        ]);

        $response = $this->post(route('posts.store', 'rescatadas'), $this->validPost());

        $post = Post::where('title', 'Mi perra rescatada no sale de debajo de la cama')->first();
        $this->assertNotNull($post);
        $this->assertNotEmpty($post->management_token);
        Storage::disk('public')->assertExists($post->photo_path);
        $response->assertRedirect(route('posts.manage', $post->management_token));

        $this->get('/comunidad/rescatadas')
            ->assertOk()
            ->assertSeeInOrder(['Mi perra rescatada no sale de debajo de la cama', 'Publicación antigua'])
            ->assertDontSee($post->management_token);

        // Enlace privado: editar y eliminar.
        $this->get(route('posts.manage', $post->management_token))
            ->assertOk()
            ->assertSee('Editar publicación')
            ->assertSee('Eliminar');

        $this->patch(route('posts.update', $post->management_token), $this->validPost([
            'title' => 'Título corregido', 'photo' => null,
        ]))->assertRedirect();
        $this->assertSame('Título corregido', $post->fresh()->title);

        $this->delete(route('posts.destroy', $post->management_token))
            ->assertRedirect(route('community.show', 'rescatadas'));
        $this->assertNull($post->fresh());
        Storage::disk('public')->assertMissing($post->photo_path);

        $this->get(route('posts.manage', 'token-inventado'))->assertNotFound();
    }

    public function test_hu17_cp3_empty_title_or_body_is_not_published(): void
    {
        $this->post(route('posts.store', 'reactivas'), $this->validPost(['title' => '', 'body' => '   ']))
            ->assertSessionHasErrors([
                'title' => 'Escribe un título para tu publicación.',
                'body' => 'Escribe el texto de tu publicación.',
            ]);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_hu17_cp4_more_than_ten_posts_are_paginated_newest_first(): void
    {
        foreach (range(1, 12) as $i) {
            Post::factory()->inTopic('salud')->create([
                'title' => sprintf('Pregunta número %02d', $i),
                'created_at' => now()->subHours(20 - $i), // la 12 es la más reciente
            ]);
        }

        $page1 = $this->get('/comunidad/salud')->assertOk()
            ->assertSeeInOrder(['Pregunta número 12', 'Pregunta número 11', 'Pregunta número 03'])
            ->assertDontSee('Pregunta número 02')
            ->assertSee('Página 1 de 2');
        $this->assertSame(10, substr_count($page1->getContent(), 'data-role="post"'));

        $this->get('/comunidad/salud?page=2')->assertOk()
            ->assertSeeInOrder(['Pregunta número 02', 'Pregunta número 01'])
            ->assertDontSee('Pregunta número 12');
    }

    public function test_hu17_cp5_unknown_topic_returns_404(): void
    {
        $this->get('/comunidad/inventado')->assertNotFound();
        $this->get('/comunidad/inventado/nueva')->assertNotFound();
        $this->post('/comunidad/inventado', $this->validPost())->assertNotFound();

        // Una publicación tampoco se puede ver desde otro tema.
        $post = Post::factory()->inTopic('salud')->create();
        $this->get('/comunidad/reactivas/'.$post->id)->assertNotFound();

        $this->assertDatabaseCount('posts', 1);
    }

    // ---------- HU-18: comentar publicaciones ----------

    public function test_hu18_cp1_comment_appears_under_post_with_date(): void
    {
        $post = Post::factory()->inTopic('entrenamiento')->create();

        $this->post(route('comments.store', ['entrenamiento', $post]), [
            'nickname' => 'Andrés',
            'body' => 'Prueba premiarlo justo cuando termina afuera.',
        ])->assertRedirect(route('posts.show', ['entrenamiento', $post]).'#comentarios');

        $comment = Comment::first();
        $this->assertSame($post->id, $comment->post_id);

        $this->get(route('posts.show', ['entrenamiento', $post]))
            ->assertOk()
            ->assertSeeInOrder([$post->title, 'Andrés', $comment->created_at->format('d/m/Y'), 'Prueba premiarlo justo cuando termina afuera.']);
    }

    public function test_hu18_cp2_helpful_adds_one_and_cannot_repeat_from_same_browser(): void
    {
        $post = Post::factory()->inTopic('alimentacion')->create(['helpful_count' => 0]);
        $cookie = $post->helpfulCookieName();

        $this->post(route('posts.helpful', ['alimentacion', $post]))
            ->assertRedirect()
            ->assertCookie($cookie);
        $this->assertSame(1, $post->fresh()->helpful_count);

        // Mismo navegador (trae la cookie): no vuelve a sumar y ya no muestra el botón.
        $this->withCookie($cookie, '1')
            ->post(route('posts.helpful', ['alimentacion', $post]))
            ->assertRedirect();
        $this->assertSame(1, $post->fresh()->helpful_count);

        $this->withCookie($cookie, '1')
            ->get(route('posts.show', ['alimentacion', $post]))
            ->assertSee('Te sirvió')
            ->assertDontSee(route('posts.helpful', ['alimentacion', $post]), false);
    }

    public function test_hu18_cp3_empty_comment_is_not_saved(): void
    {
        $post = Post::factory()->inTopic('adopcion')->create();

        $this->post(route('comments.store', ['adopcion', $post]), ['nickname' => 'Diana', 'body' => ''])
            ->assertSessionHasErrors(['body' => 'Escribe tu comentario.']);

        $this->post(route('comments.store', ['adopcion', $post]), ['nickname' => 'Diana', 'body' => 'Hola'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('comments', 0);
    }

    // ---------- HU-04: buscar y filtrar reportes ----------

    private function report(string $name, array $attributes): PetReport
    {
        return PetReport::factory()->create(array_merge([
            'pet_name' => $name, 'latitude' => 1.2136, 'longitude' => -77.2811,
        ], $attributes));
    }

    public function test_hu04_cp1_type_species_and_color_filters_are_combined(): void
    {
        $this->report('Michigris', ['type' => 'perdida', 'species' => 'gato', 'color' => 'blanco,gris']);
        $this->report('Michinegro', ['type' => 'perdida', 'species' => 'gato', 'color' => 'negro']);
        $this->report('Gatoencontrado', ['type' => 'encontrada', 'species' => 'gato', 'color' => 'gris']);
        $this->report('Perrogris', ['type' => 'perdida', 'species' => 'perro', 'color' => 'gris']);

        // Aplica tanto al listado como a los marcadores del mapa (mismo resultado).
        $response = $this->get('/reportes?type=perdida&species=gato&color=gris')->assertOk()
            ->assertSee('Michigris')
            ->assertDontSee('Michinegro')
            ->assertDontSee('Gatoencontrado')
            ->assertDontSee('Perrogris');

        $this->assertCount(1, $response->viewData('mapReports'));
        $this->assertSame('Michigris', $response->viewData('mapReports')[0]['pet_name']);
    }

    public function test_hu04_cp2_radius_shows_nearby_reports_sorted_by_distance(): void
    {
        $this->report('Pirata', ['latitude' => 1.2136 + 0.10, 'longitude' => -77.2811]);    // ~11 km
        $this->report('Quique', ['latitude' => 1.2136 + 0.02, 'longitude' => -77.2811]);    // ~2,2 km
        $this->report('Zumbi', ['latitude' => 1.2136 + 0.005, 'longitude' => -77.2811]); // ~0,6 km
        $this->report('Wasabi', ['latitude' => 1.2136 + 0.30, 'longitude' => -77.2811]); // ~33 km

        $this->get('/reportes?lat=1.2136&lng=-77.2811&radius=5')->assertOk()
            ->assertSeeInOrder(['Zumbi', 'Quique'])
            ->assertDontSee('Pirata')
            ->assertSee('a menos de 5 km de ti');

        // El radio se limita a 1–20 km: pedir 100 km no muestra el de ~33 km.
        $this->get('/reportes?lat=1.2136&lng=-77.2811&radius=100')->assertOk()
            ->assertSeeInOrder(['Zumbi', 'Quique', 'Pirata'])
            ->assertDontSee('Wasabi');
    }

    public function test_hu04_cp3_no_results_shows_message_and_clear_button(): void
    {
        $this->report('Firulais', ['type' => 'perdida', 'species' => 'perro', 'color' => 'cafe']);

        $this->get('/reportes?species=ave')->assertOk()
            ->assertSee('No hay reportes con esos filtros')
            ->assertSee('Limpiar filtros')
            ->assertSee('href="'.route('reports.index').'"', false)
            ->assertDontSee('Firulais');
    }

    public function test_hu04_cp4_filters_are_kept_in_the_url(): void
    {
        $response = $this->get('/reportes?type=encontrada&species=gato&color=negro&lat=1.2136&lng=-77.2811&radius=10')->assertOk();

        $response->assertSee('id="report-filters"', false)
            ->assertSee('method="GET"', false)
            ->assertSee('<option value="encontrada" selected', false)
            ->assertSee('<option value="gato" selected', false)
            ->assertSee('<option value="negro" selected', false)
            ->assertSee('<option value="10" selected', false)
            ->assertSee('name="lat" value="1.2136"', false);

        // Valores inventados en la URL se ignoran en vez de romper la página.
        $this->get('/reportes?species=mesa&color=fucsia')->assertOk()
            ->assertDontSee('No hay reportes con esos filtros');
    }
}
