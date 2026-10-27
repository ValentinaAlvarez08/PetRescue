<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Sprint 3 — HU-17 / HU-18: publicaciones de la comunidad.
 * Igual que los reportes, no hay login: quien publica recibe un enlace
 * privado con el token para editar o eliminar su publicación.
 */
class PostController extends Controller
{
    /** Duración de la cookie de "Me sirvió": un año, en minutos. */
    private const HELPFUL_COOKIE_MINUTES = 60 * 24 * 365;

    /** HU-17: formulario "Nueva publicación". */
    public function create(string $topic): View
    {
        return view('posts.create', [
            'slug' => $topic,
            'topic' => $this->topicOrFail($topic),
        ]);
    }

    /** HU-17: guarda la publicación y entrega el enlace privado. */
    public function store(StorePostRequest $request, string $topic): RedirectResponse
    {
        $this->topicOrFail($topic);

        $data = $request->postData();
        $data['topic'] = $topic;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('community-posts', 'public');
        }

        $post = Post::create($data);

        return redirect()
            ->route('posts.manage', $post->management_token)
            ->with('status', 'Publicación creada. Guarda este enlace privado: es la única forma de editarla o eliminarla.');
    }

    /** HU-18: detalle público con comentarios y botón "Me sirvió". */
    public function show(Request $request, string $topic, Post $post): View
    {
        // HU-20: una publicación oculta por denuncias no se muestra.
        abort_unless($post->topic === $topic && ! $post->isHidden(), 404);
        $post->load('comments');

        return view('posts.show', [
            'post' => $post,
            'slug' => $topic,
            'topic' => $this->topicOrFail($topic),
            'alreadyHelpful' => $request->cookie($post->helpfulCookieName()) !== null,
        ]);
    }

    /**
     * HU-18: "Me sirvió" suma 1. El servidor revisa una cookie por
     * publicación, así el mismo navegador no puede volver a sumar.
     */
    public function helpful(Request $request, string $topic, Post $post): RedirectResponse
    {
        abort_unless($post->topic === $topic, 404);
        $redirect = redirect()->route('posts.show', [$topic, $post]);

        if ($request->cookie($post->helpfulCookieName()) !== null) {
            return $redirect->with('status', 'Ya habías marcado esta publicación como útil.');
        }

        $post->increment('helpful_count');

        return $redirect
            ->with('status', '¡Gracias! Marcaste esta publicación como útil.')
            ->withCookie(cookie($post->helpfulCookieName(), '1', self::HELPFUL_COOKIE_MINUTES));
    }

    /** HU-17: vista privada para editar o eliminar (solo con el token). */
    public function manage(string $token): View
    {
        $post = Post::where('management_token', $token)->firstOrFail();

        return view('posts.manage', [
            'post' => $post,
            'topic' => $post->topic_data,
        ]);
    }

    public function update(StorePostRequest $request, string $token): RedirectResponse
    {
        $post = Post::where('management_token', $token)->firstOrFail();
        $data = $request->postData();

        if ($request->hasFile('photo') || $request->boolean('remove_photo')) {
            $this->deletePhoto($post);
            $data['photo_path'] = $request->hasFile('photo')
                ? $request->file('photo')->store('community-posts', 'public')
                : null;
        }

        $post->update($data);

        return back()->with('status', 'Publicación actualizada.');
    }

    public function destroy(string $token): RedirectResponse
    {
        $post = Post::where('management_token', $token)->firstOrFail();
        $topic = $post->topic;

        $this->deletePhoto($post);
        $post->delete(); // los comentarios se eliminan en cascada

        return redirect()
            ->route('community.show', $topic)
            ->with('status', 'Tu publicación fue eliminada.');
    }

    /** HU-17: un tema inventado responde 404. */
    private function topicOrFail(string $topic): array
    {
        $topics = config('community.topics');
        abort_unless(array_key_exists($topic, $topics), 404);

        return $topics[$topic];
    }

    /** Borra la foto subida (las de datos de ejemplo viven en public/images/). */
    private function deletePhoto(Post $post): void
    {
        if ($post->photo_path && ! Str::startsWith($post->photo_path, 'images/')) {
            Storage::disk('public')->delete($post->photo_path);
        }
    }
}
