@extends('layouts.app')

@section('title', 'Guías rápidas para cuidar a tu mascota — PetRescue')

@section('content')
{{-- Sprint 4 — HU-21: guías rápidas por tema --}}
<div class="mb-6">
    <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Guías rápidas</p>
    <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Qué hacer, paso a paso</h1>
    <p class="text-ink-500 mt-1 max-w-2xl">Consejos cortos para los momentos en que más se necesitan. No reemplazan la consulta con tu veterinario.</p>
</div>

<nav class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4" aria-label="Temas">
    @php $chip = fn ($on) => 'shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold border '.($on ? 'bg-ink-900 text-white border-ink-900' : 'bg-white border-ink-200 hover:bg-ink-100'); @endphp
    <a href="{{ route('guides.index') }}" class="{{ $chip(! $selected) }}">Todas</a>
    @foreach ($topics as $key => $topic)
        <a href="{{ route('guides.index', ['tema' => $key]) }}" class="{{ $chip($selected === $key) }}" data-topic="{{ $key }}">
            <span>{{ $topic['emoji'] }}</span>{{ $topic['label'] }}
            <span class="text-xs opacity-70">{{ $countsByTopic[$key] ?? 0 }}</span>
        </a>
    @endforeach
</nav>

<input id="guide-search" type="search" placeholder="Buscar una guía (ej: calor, vacunas, pólvora)..."
       class="w-full sm:w-96 mb-6 rounded-xl border border-ink-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400">

<div id="guides" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($guides as $slug => $guide)
        @php $topic = $topics[$guide['topic']]; @endphp
        <a href="{{ route('guides.show', $slug) }}" data-role="guide"
           data-text="{{ mb_strtolower($guide['title'].' '.$guide['summary'].' '.implode(' ', $guide['steps'])) }}"
           class="flex flex-col rounded-3xl bg-white border border-ink-100 shadow-soft p-5 hover:-translate-y-0.5 hover:shadow-lg transition">
            <div class="flex items-center justify-between gap-2">
                <span class="w-12 h-12 rounded-2xl grid place-items-center text-2xl" style="background: {{ $topic['color'] }}1f">{{ $guide['emoji'] }}</span>
                <span class="text-xs font-extrabold" style="color: {{ $topic['color'] }}">{{ $topic['emoji'] }} {{ $topic['label'] }}</span>
            </div>
            <h2 class="font-extrabold text-lg leading-tight mt-3">{{ $guide['title'] }}</h2>
            <p class="text-sm text-ink-500 mt-1 flex-1">{{ $guide['summary'] }}</p>
            <p class="text-xs font-bold text-ink-500 mt-3">⏱️ {{ $guide['minutes'] }} min de lectura · {{ count($guide['steps']) }} pasos</p>
        </a>
    @endforeach
</div>
<p id="no-guides" class="hidden text-center text-ink-500 py-10">No encontramos guías con esa palabra.</p>
@endsection

@push('scripts')
<script>
(function () {
    const cards = [...document.querySelectorAll('[data-role="guide"]')];
    document.getElementById('guide-search').addEventListener('input', e => {
        const q = e.target.value.trim().toLowerCase();
        let shown = 0;
        cards.forEach(c => {
            const on = !q || c.dataset.text.includes(q);
            c.classList.toggle('hidden', !on);
            shown += on ? 1 : 0;
        });
        document.getElementById('no-guides').classList.toggle('hidden', shown > 0);
    });
})();
</script>
@endpush
