@extends('layouts.app')

@section('title', $topic['label'].' — Comunidad PetRescue')

@section('content')
<a href="{{ route('home') }}#comunidad" class="text-sm font-bold text-ink-500 hover:text-ink-900">← Comunidad</a>

<div class="mt-4 grid lg:grid-cols-[1fr_300px] gap-6">
    <div>
        <div class="rounded-3xl bg-white border border-ink-100 shadow-soft p-8 flex flex-col sm:flex-row sm:items-end justify-between gap-5">
            <div>
                <span class="w-16 h-16 rounded-2xl bg-brand-50 grid place-items-center text-4xl">{{ $topic['emoji'] }}</span>
                <h1 class="text-3xl font-black mt-4">{{ $topic['label'] }}</h1>
                <p class="text-ink-500 mt-2 text-lg">{{ $topic['blurb'] }}</p>
            </div>
            {{-- HU-17: publicar en el tema --}}
            <a href="{{ route('posts.create', $slug) }}"
               class="shrink-0 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold px-5 py-3 text-center">✍️ Nueva publicación</a>
        </div>

        {{-- HU-17: publicaciones del tema, de la más reciente a la más antigua --}}
        <div class="mt-6 space-y-4">
            @forelse ($posts as $post)
                <a href="{{ route('posts.show', [$slug, $post]) }}"
                   class="flex gap-4 rounded-3xl bg-white border border-ink-100 shadow-soft p-5 hover:border-brand-200 hover:bg-brand-50/40 transition"
                   data-role="post">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-ink-500">
                            <span class="font-bold text-ink-700">{{ $post->nickname }}</span> · {{ $post->created_at->locale('es')->diffForHumans() }}
                        </p>
                        <h2 class="font-extrabold text-lg leading-tight mt-1">{{ $post->title }}</h2>
                        <p class="text-sm text-ink-700 mt-1 line-clamp-2">{{ $post->body }}</p>
                        <p class="text-xs font-bold text-ink-500 mt-3 flex gap-4">
                            <span>💬 {{ $post->comments_count }} {{ $post->comments_count === 1 ? 'comentario' : 'comentarios' }}</span>
                            <span>👍 {{ $post->helpful_count }} me sirvió</span>
                        </p>
                    </div>
                    @if ($post->photo_url)
                        <img src="{{ $post->photo_url }}" alt="" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover shrink-0" loading="lazy">
                    @endif
                </a>
            @empty
                <div class="rounded-3xl border-2 border-dashed border-brand-200 bg-brand-50/60 p-8 text-center">
                    <p class="text-4xl">💬</p>
                    <p class="font-extrabold text-lg mt-2">Todavía no hay publicaciones en este tema</p>
                    <p class="text-ink-500 mt-1 max-w-lg mx-auto">
                        Cuenta tu experiencia o haz la primera pregunta. No necesitas crear una cuenta.
                    </p>
                    <a href="{{ route('posts.create', $slug) }}"
                       class="inline-block mt-4 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold px-5 py-3">Nueva publicación</a>
                </div>
            @endforelse
        </div>

        {{-- HU-17: paginación (10 por página) --}}
        @if ($posts->hasPages())
            <nav class="mt-6 flex items-center justify-between gap-3 text-sm font-bold" aria-label="Paginación">
                @if ($posts->onFirstPage())
                    <span class="px-4 py-2 rounded-xl border border-ink-100 text-ink-200">← Más recientes</span>
                @else
                    <a href="{{ $posts->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-ink-200 bg-white hover:bg-ink-100">← Más recientes</a>
                @endif
                <span class="text-ink-500">Página {{ $posts->currentPage() }} de {{ $posts->lastPage() }}</span>
                @if ($posts->hasMorePages())
                    <a href="{{ $posts->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-ink-200 bg-white hover:bg-ink-100">Más antiguas →</a>
                @else
                    <span class="px-4 py-2 rounded-xl border border-ink-100 text-ink-200">Más antiguas →</span>
                @endif
            </nav>
        @endif
    </div>

    <aside class="rounded-3xl bg-white border border-ink-100 shadow-soft p-5 h-fit">
        <p class="font-extrabold mb-3">Otros temas</p>
        <ul class="space-y-1">
            @foreach ($topics as $key => $t)
                <li>
                    <a href="{{ route('community.show', $key) }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold {{ $key === $slug ? 'bg-brand-50 text-brand-800' : 'hover:bg-ink-50' }}">
                        <span>{{ $t['emoji'] }}</span>{{ $t['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>
</div>
@endsection
