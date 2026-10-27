@extends('layouts.app')

{{-- Sprint 4 — HU-20: revisión de contenido oculto (solo con el enlace firmado del correo de moderación) --}}
@section('title', 'Revisar contenido denunciado — PetRescue')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="max-w-2xl mx-auto">
    <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Moderación</p>
    <h1 class="text-3xl font-black mt-1">Revisar {{ $content->flagLabel() }}</h1>
    <p class="mt-2">
        <span class="text-xs font-extrabold uppercase px-3 py-1 rounded-full {{ $content->isHidden() ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}" data-role="flag-status">
            {{ $content->isHidden() ? 'Oculto' : 'Visible' }}
        </span>
    </p>

    <div class="mt-6 bg-white rounded-3xl shadow-soft border border-ink-100 p-6 text-sm space-y-2">
        @switch($type)
            @case('reporte')
                <p class="font-bold">{{ $content->display_name }} ({{ $content->species_label }}, {{ $content->type }})</p>
                @if ($content->photo_url)<img src="{{ $content->photo_url }}" alt="" class="w-48 rounded-xl">@endif
                <p class="whitespace-pre-line">{{ $content->description }}</p>
                <p class="text-ink-500">Contacto: {{ $content->contact_phone }}{{ $content->contact_email ? ' · '.$content->contact_email : '' }}</p>
                @break
            @case('publicacion')
                <p class="font-bold">{{ $content->title }} — {{ $content->nickname }}</p>
                @if ($content->photo_url)<img src="{{ $content->photo_url }}" alt="" class="w-48 rounded-xl">@endif
                <p class="whitespace-pre-line">{{ $content->body }}</p>
                @break
            @default
                <p class="font-bold">{{ $content->nickname }}</p>
                <p class="whitespace-pre-line">{{ $content->body }}</p>
        @endswitch
    </div>

    <h2 class="text-xl font-black mt-8">Denuncias ({{ $flags->count() }})</h2>
    <ul class="mt-3 space-y-2">
        @forelse ($flags as $flag)
            <li class="rounded-2xl bg-white border border-ink-100 p-4 text-sm">
                <span class="font-bold">{{ $flag->reason_label }}</span>
                <span class="text-ink-500">· {{ $flag->created_at->format('d/m/Y H:i') }}</span>
                @if ($flag->details)<p class="mt-1">{{ $flag->details }}</p>@endif
            </li>
        @empty
            <li class="text-ink-500">No hay denuncias.</li>
        @endforelse
    </ul>

    @if ($content->isHidden())
        <form method="POST" action="{{ $action }}" class="mt-6 rounded-3xl bg-white border border-ink-100 shadow-soft p-6">
            @csrf
            <p class="text-sm text-ink-700">Si no infringe las normas, vuelve a mostrarlo. Se borrarán sus denuncias.
                Si sí las infringe, no hagas nada: seguirá oculto.</p>
            <button class="mt-4 w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3">Volver a mostrar</button>
        </form>
    @endif
</div>
@endsection
