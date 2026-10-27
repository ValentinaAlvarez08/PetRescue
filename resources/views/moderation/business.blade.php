@extends('layouts.app')

{{-- Sprint 4 — HU-19: revisión de la solicitud (solo con el enlace firmado del correo de moderación) --}}
@php
    $info = $business->category_info;
    $statusLabel = ['pendiente' => 'Pendiente', 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado'][$business->review_status] ?? $business->review_status;
@endphp

@section('title', 'Revisar negocio — PetRescue')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="max-w-2xl mx-auto">
    <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Moderación</p>
    <h1 class="text-3xl font-black mt-1">Solicitud: {{ $business->name }}</h1>
    <p class="mt-2"><span class="text-xs font-extrabold uppercase px-3 py-1 rounded-full bg-ink-100" data-role="review-status">{{ $statusLabel }}</span></p>

    <div class="mt-6 bg-white rounded-3xl shadow-soft border border-ink-100 p-6 space-y-2 text-sm">
        <p><span class="font-bold">Categoría:</span> {{ $info['emoji'] }} {{ $info['label'] }}</p>
        <p><span class="font-bold">Descripción:</span> {{ $business->description }}</p>
        @if ($business->services)
            <p><span class="font-bold">Servicios:</span> {{ $business->services }}</p>
        @endif
        <p><span class="font-bold">Dirección:</span> {{ $business->address }}
            (<a class="text-brand-700 underline" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ $business->latitude }},{{ $business->longitude }}">ver en el mapa</a>)</p>
        <p><span class="font-bold">Teléfono:</span> {{ $business->phone }}{{ $business->has_whatsapp ? ' (WhatsApp)' : '' }}</p>
        @if ($business->email)<p><span class="font-bold">Correo:</span> {{ $business->email }}</p>@endif
        @if ($business->schedule)<p><span class="font-bold">Horario:</span> {{ $business->schedule }}</p>@endif
        <p class="pt-2 border-t border-ink-100"><span class="font-bold">Solicitado por:</span> {{ $business->requester_name }} &lt;{{ $business->requester_email }}&gt;, {{ $business->created_at->format('d/m/Y H:i') }}</p>
    </div>

    @if ($business->review_status === 'pendiente')
        <form method="POST" action="{{ $action }}" class="mt-6 bg-white rounded-3xl shadow-soft border border-ink-100 p-6 space-y-4">
            @csrf
            <div>
                <label for="note" class="block text-sm font-bold mb-1">Motivo si lo rechazas <span class="font-normal text-ink-500">(se le envía a quien lo solicitó)</span></label>
                <textarea id="note" name="note" rows="2" maxlength="300" class="w-full rounded-xl border border-ink-200 px-4 py-3"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <button name="decision" value="aprobar" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3">✅ Aprobar</button>
                <button name="decision" value="rechazar" class="rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold py-3">Rechazar</button>
            </div>
        </form>
    @else
        <p class="mt-6 text-ink-500">Esta solicitud ya fue revisada el {{ optional($business->reviewed_at)->format('d/m/Y H:i') }}.</p>
    @endif
</div>
@endsection
