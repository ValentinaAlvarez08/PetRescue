@extends('layouts.app')

@section('title', $guide['title'].' — Guías PetRescue')

@push('head')
    <meta name="description" content="{{ $guide['summary'] }}">
@endpush

@section('content')
{{-- Sprint 4 — HU-21: detalle de la guía --}}
<div class="max-w-3xl mx-auto">
    <a href="{{ route('guides.index', ['tema' => $guide['topic']]) }}" class="text-sm font-bold text-ink-500 hover:text-ink-900">← {{ $topic['emoji'] }} {{ $topic['label'] }}</a>

    <article class="mt-4 rounded-3xl bg-white border border-ink-100 shadow-soft p-6 sm:p-8">
        <span class="w-16 h-16 rounded-2xl grid place-items-center text-4xl" style="background: {{ $topic['color'] }}1f">{{ $guide['emoji'] }}</span>
        <h1 class="text-2xl sm:text-3xl font-black mt-4 leading-tight">{{ $guide['title'] }}</h1>
        <p class="text-ink-500 mt-2">{{ $guide['summary'] }} · ⏱️ {{ $guide['minutes'] }} min</p>

        <ol class="mt-6 space-y-4">
            @foreach ($guide['steps'] as $i => $step)
                <li class="flex gap-4" data-role="guide-step">
                    <span class="w-8 h-8 shrink-0 rounded-full grid place-items-center font-black text-white" style="background: {{ $topic['color'] }}">{{ $i + 1 }}</span>
                    <p class="text-ink-700 leading-relaxed pt-1">{{ $step }}</p>
                </li>
            @endforeach
        </ol>

        @if ($guide['warning'])
            <div class="mt-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-amber-900">
                <p class="font-extrabold">⚠️ Importante</p>
                <p class="text-sm mt-1">{{ $guide['warning'] }}</p>
            </div>
        @endif

        <div class="mt-6 flex flex-wrap gap-2">
            @if ($dir = $guide['related']['directory'] ?? null)
                @php $cat = config("community.directory.{$dir}"); @endphp
                <a href="{{ route('directory.index', ['categoria' => $dir]) }}" class="rounded-xl border border-ink-200 hover:bg-ink-50 font-bold px-4 py-2 text-sm">
                    {{ $cat['emoji'] }} {{ $cat['short'] }} cerca de ti
                </a>
            @endif
            @if ($topicKey = $guide['related']['community'] ?? null)
                @php $community = config("community.topics.{$topicKey}"); @endphp
                <a href="{{ route('community.show', $topicKey) }}" class="rounded-xl border border-ink-200 hover:bg-ink-50 font-bold px-4 py-2 text-sm">
                    💬 Pregunta en «{{ $community['label'] }}»
                </a>
            @endif
            <button type="button" onclick="window.print()" class="rounded-xl border border-ink-200 hover:bg-ink-50 font-bold px-4 py-2 text-sm">🖨️ Imprimir</button>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <h2 class="text-xl font-black mt-8 mb-3">Más guías de {{ mb_strtolower($topic['label']) }}</h2>
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach ($related as $relatedSlug => $other)
                <a href="{{ route('guides.show', $relatedSlug) }}" class="flex gap-3 items-center rounded-2xl bg-white border border-ink-100 shadow-soft p-4 hover:border-brand-200">
                    <span class="text-2xl">{{ $other['emoji'] }}</span>
                    <span class="font-bold leading-tight">{{ $other['title'] }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
