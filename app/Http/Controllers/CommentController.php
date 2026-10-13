<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

/**
 * Sprint 3 — HU-18: comentarios de las publicaciones de la comunidad.
 */
class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, string $topic, Post $post): RedirectResponse
    {
        abort_unless($post->topic === $topic, 404);

        $post->comments()->create($request->validated());

        return redirect()
            ->to(route('posts.show', [$topic, $post]).'#comentarios')
            ->with('status', 'Tu comentario fue publicado.');
    }
}
