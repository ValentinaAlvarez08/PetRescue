<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

/**
 * Espacios de conversación de la comunidad.
 * Sprint 2 (HU-15): la página del tema existe y se enlaza desde el inicio.
 * Sprint 3 (HU-17): listado de publicaciones del tema, paginado.
 */
class CommunityController extends Controller
{
    /** Publicaciones por página en un tema (HU-17, criterio 4). */
    public const PER_PAGE = 10;

    public function show(string $topic): View
    {
        $topics = config('community.topics');
        // HU-17: solo existen los temas de config/community.php.
        abort_unless(array_key_exists($topic, $topics), 404);

        // De la más reciente a la más antigua; el id desempata publicaciones del mismo segundo.
        $posts = Post::inTopic($topic)
            ->visible() // HU-20: sin las ocultas por denuncias
            ->withCount('comments')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        return view('community.show', [
            'slug' => $topic,
            'topic' => $topics[$topic],
            'topics' => $topics,
            'posts' => $posts,
        ]);
    }
}
