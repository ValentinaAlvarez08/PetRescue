<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Espacios de conversación de la comunidad.
 * Sprint 2 (HU-15): la página del tema existe y se enlaza desde el inicio.
 * Sprint 3 (HU-17 / HU-18): se habilita publicar y comentar.
 */
class CommunityController extends Controller
{
    public function show(string $topic): View
    {
        $topics = config('community.topics');
        abort_unless(array_key_exists($topic, $topics), 404);

        return view('community.show', [
            'slug' => $topic,
            'topic' => $topics[$topic],
            'topics' => $topics,
        ]);
    }
}
