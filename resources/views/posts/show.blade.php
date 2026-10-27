@extends('layouts.app')

@section('title', $post->title.' — Comunidad PetRescue')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('community.show', $slug) }}" class="text-sm font-bold text-ink-500 hover:text-ink-900">← {{ $topic['emoji'] }} {{ $topic['label'] }}</a>

    {{-- HU-17: detalle de la publicación --}}
    <article class="mt-4 rounded-3xl bg-white border border-ink-100 shadow-soft overflow-hidden">
        @if ($post->photo_url)
            <img src="{{ $post->photo_url }}" alt="" class="w-full max-h-96 object-cover">
        @endif
        <div class="p-6 sm:p-8">
            <p class="text-sm text-ink-500">
                <span class="font-bold text-ink-700">{{ $post->nickname }}</span> ·
                <time datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->created_at->locale('es')->translatedFormat('j \d\e F \d\e Y') }}</time>
            </p>
            <h1 class="text-2xl sm:text-3xl font-black mt-2 leading-tight">{{ $post->title }}</h1>
            <div class="mt-4 text-ink-700 leading-relaxed whitespace-pre-line">{{ $post->body }}</div>

            {{-- HU-18: "Me sirvió" (una vez por navegador, controlado con cookie en el servidor) --}}
            <div class="mt-6 flex items-center gap-3">
                @if ($alreadyHelpful)
                    <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold px-4 py-2">
                        ✅ Te sirvió · <span data-role="helpful-count">{{ $post->helpful_count }}</span>
                    </span>
                @else
                    <form action="{{ route('posts.helpful', [$slug, $post]) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-50 hover:bg-brand-100 border border-brand-200 text-brand-800 font-bold px-4 py-2">
                            👍 Me sirvió · <span data-role="helpful-count">{{ $post->helpful_count }}</span>
                        </button>
                    </form>
                @endif
                <span class="text-sm text-ink-500">{{ $post->helpful_count === 1 ? 'persona encontró' : 'personas encontraron' }} útil esta publicación</span>
            </div>

            {{-- Sprint 4 — HU-20 --}}
            <x-flag-form class="mt-6 pt-4 border-t border-ink-100" :action="route('flags.post', [$slug, $post])" target="publicacion-{{ $post->id }}"
                         label="Denunciar esta publicación" />
        </div>
    </article>

    {{-- HU-18: comentarios --}}
    <section id="comentarios" class="mt-8 scroll-mt-24">
        <h2 class="text-xl font-black mb-4">
            {{ $post->comments->count() }} {{ $post->comments->count() === 1 ? 'comentario' : 'comentarios' }}
        </h2>

        <div class="space-y-3">
            @forelse ($post->comments as $comment)
                <div id="comentario-{{ $comment->id }}" class="rounded-2xl bg-white border border-ink-100 shadow-soft p-4 scroll-mt-24" data-role="comment">
                    <p class="text-sm">
                        <span class="font-bold">{{ $comment->nickname }}</span>
                        <time class="text-ink-500" datetime="{{ $comment->created_at->toIso8601String() }}">· {{ $comment->created_at->format('d/m/Y H:i') }}</time>
                    </p>
                    <p class="text-ink-700 mt-1 whitespace-pre-line">{{ $comment->body }}</p>
                    {{-- Sprint 4 — HU-20 --}}
                    <x-flag-form class="mt-2 text-xs" :action="route('flags.comment', $comment)" target="comentario-{{ $comment->id }}" />
                </div>
            @empty
                <p class="text-ink-500">Nadie ha comentado todavía. ¡Sé la primera persona en ayudar!</p>
            @endforelse
        </div>

        @php
            $input = 'w-full rounded-xl border border-ink-200 bg-ink-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-400';
        @endphp
        <form action="{{ route('comments.store', [$slug, $post]) }}#comentarios" method="POST" novalidate
              class="mt-6 rounded-3xl bg-white border border-ink-100 shadow-soft p-5 sm:p-6 space-y-4">
            @csrf
            <p class="font-extrabold">Deja tu comentario</p>
            <div>
                <label for="nickname" class="block text-sm font-bold mb-1">Tu apodo</label>
                <input id="nickname" name="nickname" type="text" maxlength="40" value="{{ old('nickname') }}"
                       class="{{ $input }} {{ $errors->has('nickname') ? 'border-rose-400 bg-rose-50' : '' }}">
                @error('nickname') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="comment-body" class="block text-sm font-bold mb-1">Comentario</label>
                <textarea id="comment-body" name="body" rows="3" maxlength="1000" placeholder="Comparte un consejo o tu experiencia (5 a 1000 caracteres)"
                          class="{{ $input }} {{ $errors->has('body') ? 'border-rose-400 bg-rose-50' : '' }}">{{ old('body') }}</textarea>
                @error('body') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-3 rounded-xl font-bold text-white bg-brand-500 hover:bg-brand-600">Comentar</button>
            </div>
        </form>
    </section>
</div>
@endsection
