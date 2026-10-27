<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Sprint 4 — HU-21: guías rápidas por tema (contenido en config/guides.php).
 */
class GuideController extends Controller
{
    public function index(Request $request): View
    {
        $topics = config('guides.topics');
        $guides = collect(config('guides.guides'));

        // Un tema que no existe se ignora y se muestran todas.
        $topic = array_key_exists((string) $request->query('tema'), $topics) ? $request->query('tema') : null;

        return view('guides.index', [
            'topics' => $topics,
            'selected' => $topic,
            'guides' => $topic ? $guides->where('topic', $topic) : $guides,
            'countsByTopic' => $guides->countBy('topic'),
        ]);
    }

    public function show(string $slug): View
    {
        $guide = config("guides.guides.{$slug}");
        abort_unless(is_array($guide), 404);

        return view('guides.show', [
            'slug' => $slug,
            'guide' => $guide,
            'topic' => config("guides.topics.{$guide['topic']}"),
            // Otras guías del mismo tema.
            'related' => collect(config('guides.guides'))
                ->where('topic', $guide['topic'])
                ->except($slug),
        ]);
    }
}
