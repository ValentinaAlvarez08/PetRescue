@extends('layouts.app')

{{-- Sprint 4 — HU-10 (CID 2): perfil público, el que abre quien escanea el QR de la placa --}}
@php
    $lostReport = $profile->activeLostReport;
    $waText = rawurlencode("Hola, {$profile->owner_name}. Encontré a {$profile->name} (vi su perfil en PetRescue): ".$profile->public_url);
@endphp

@section('title', $profile->name.' — Perfil digital PetRescue')

@push('head')
    <meta property="og:title" content="{{ $profile->name }} ({{ $profile->species_label }}) — PetRescue">
    <meta property="og:description" content="Si me encontraste, contacta a mi familia desde aquí.">
    <meta property="og:image" content="{{ url($profile->photo_url) }}">
    <meta name="robots" content="noindex">
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    @if ($lostReport)
        <a href="{{ route('reports.show', $lostReport) }}" data-role="lost-banner"
           class="mb-5 flex items-center justify-between gap-4 rounded-2xl bg-rose-500 hover:bg-rose-600 text-white p-5 shadow-soft">
            <span>
                <span class="block text-xl font-black">🚨 ¡{{ $profile->name }} está perdida!</span>
                <span class="text-white/90 text-sm">Su familia la está buscando desde el {{ optional($lostReport->event_date)->format('d/m/Y') ?? $lostReport->created_at->format('d/m/Y') }}. Si la ves, avísales.</span>
            </span>
            <span class="font-extrabold whitespace-nowrap">Ver reporte →</span>
        </a>
    @endif

    <div class="grid md:grid-cols-[1fr_1.1fr] gap-6">
        <div class="bg-white rounded-3xl shadow-soft border border-ink-100 overflow-hidden">
            <img src="{{ $profile->photo_url }}" alt="Foto de {{ $profile->name }}" class="w-full aspect-square object-cover">
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-3xl shadow-soft border border-ink-100 p-6">
                <p class="text-xs font-extrabold uppercase tracking-wider text-brand-600">🪪 Perfil digital</p>
                <h1 class="text-4xl font-black mt-1">{{ $profile->name }}</h1>
                <p class="text-ink-500 mt-1">{{ $profile->species_emoji }} {{ $profile->species_label }}{{ $profile->breed ? ' · '.$profile->breed : '' }}</p>

                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-ink-50 p-3"><dt class="text-ink-500 text-xs font-bold uppercase">Sexo · tamaño</dt>
                        <dd class="font-bold">{{ config('pets.sexes.'.$profile->sex, '—') }}{{ $profile->size ? ' · '.ucfirst($profile->size) : '' }}</dd></div>
                    <div class="rounded-xl bg-ink-50 p-3"><dt class="text-ink-500 text-xs font-bold uppercase">Edad</dt>
                        <dd class="font-bold">{{ $profile->age_label ?? 'No registrada' }}</dd></div>
                    <div class="rounded-xl bg-ink-50 p-3 col-span-2"><dt class="text-ink-500 text-xs font-bold uppercase">Color</dt>
                        <dd class="font-bold">{{ $profile->color_labels }}</dd></div>
                    @if ($profile->distinctive_marks)
                        <div class="rounded-xl bg-ink-50 p-3 col-span-2"><dt class="text-ink-500 text-xs font-bold uppercase">Señas particulares</dt>
                            <dd>{{ $profile->distinctive_marks }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-3xl shadow-soft border border-ink-100 p-6">
                <h2 class="font-extrabold text-lg">❤️ Salud</h2>
                <ul class="mt-2 text-sm space-y-1.5">
                    <li><span class="font-bold">Esterilizada:</span> {{ config('pets.sterilized.'.$profile->sterilized, 'No sé') }}</li>
                    @if ($profile->vaccines)
                        <li><span class="font-bold">Vacunas:</span> {{ $profile->vaccines }}</li>
                    @endif
                    @if ($profile->health_notes)
                        <li class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-amber-900"><span class="font-bold">⚠️ Importante:</span> {{ $profile->health_notes }}</li>
                    @endif
                </ul>
            </div>

            <div class="bg-white rounded-3xl shadow-soft border border-ink-100 p-6">
                <h2 class="font-extrabold text-lg">¿Me encontraste?</h2>
                <p class="text-sm text-ink-500 mt-1">Contacta a {{ $profile->owner_name }}{{ $profile->neighborhood ? ' (barrio '.$profile->neighborhood.')' : '' }}.</p>
                <div class="mt-4 grid gap-2">
                    @if ($profile->contact_whatsapp && $profile->whatsapp_number)
                        <a href="https://wa.me/{{ $profile->whatsapp_number }}?text={{ $waText }}" target="_blank" rel="noopener"
                           class="flex items-center justify-center gap-2 rounded-xl bg-[#25D366] hover:brightness-95 text-white font-extrabold py-3">💬 Escribir por WhatsApp</a>
                    @endif
                    <a href="tel:{{ $profile->contact_phone }}" class="flex items-center justify-center gap-2 rounded-xl bg-ink-900 hover:bg-ink-700 text-white font-extrabold py-3">📞 Llamar</a>
                    @if ($profile->contact_email)
                        <a href="mailto:{{ $profile->contact_email }}?subject={{ rawurlencode('Encontré a '.$profile->name) }}"
                           class="flex items-center justify-center gap-2 rounded-xl border border-ink-200 hover:bg-ink-50 font-bold py-3">✉️ Enviar correo</a>
                    @endif
                    <a href="{{ route('reports.create', 'encontrada') }}" class="text-center text-sm font-bold text-brand-700 hover:underline mt-1">
                        ¿No contestan? Publica un reporte de mascota encontrada →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
