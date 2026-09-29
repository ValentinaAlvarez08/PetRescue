@extends('layouts.app')

@php
    $lost = $report->type === 'perdida';
    $publicUrl = route('reports.show', $report);
    $waText = rawurlencode(($lost
        ? "Hola, vi en PetRescue el reporte de {$report->display_name} ({$report->species_label}) y creo que tengo información: "
        : "Hola, vi en PetRescue el reporte de la mascota que encontraste ({$report->species_label}) y creo que puede ser mía: ").$publicUrl);
    $statusLabel = ['activo' => 'Activo', 'reunido' => '¡Reunido!', 'cerrado' => 'Cerrado'][$report->status] ?? $report->status;
@endphp

@section('title', $report->display_name.' — PetRescue')

@section('content')
<a href="{{ route('reports.index') }}" class="text-sm font-bold text-ink-500 hover:text-ink-900">← Todos los reportes</a>

@if ($canManage)
    <div class="mt-4 rounded-2xl border-2 border-dashed border-brand-300 bg-brand-50 p-5">
        <p class="font-extrabold text-brand-900">🔐 Este es tu enlace privado de gestión</p>
        <p class="text-sm text-brand-900/80 mt-1">Guárdalo: es la única forma de actualizar o cerrar este reporte. No lo compartas.
            Para difundir el caso usa el enlace público.</p>
        <div class="mt-3 flex flex-col sm:flex-row gap-2">
            <input readonly value="{{ url()->current() }}" class="flex-1 rounded-xl border border-brand-200 bg-white px-3 py-2 text-sm" onclick="this.select()">
            <button type="button" data-copy="{{ url()->current() }}" class="rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold px-4 py-2 text-sm">Copiar enlace privado</button>
        </div>
    </div>
@endif

<div class="mt-4 grid lg:grid-cols-[1.2fr_1fr] gap-6">
    <article class="bg-white rounded-3xl shadow-soft border border-ink-100 overflow-hidden">
        @if ($report->photo_path)
            <img src="{{ Storage::url($report->photo_path) }}" alt="Foto de {{ $report->display_name }}" class="w-full max-h-[28rem] object-cover">
        @else
            <div class="h-56 grid place-items-center bg-ink-100 text-7xl">{{ $report->species_emoji }}</div>
        @endif

        <div class="p-6">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full {{ $lost ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $lost ? 'Perdida' : 'Encontrada' }}
                </span>
                <span class="text-xs font-bold px-3 py-1 rounded-full {{ $report->status === 'reunido' ? 'bg-emerald-500 text-white' : 'bg-ink-100 text-ink-700' }}">{{ $statusLabel }}</span>
            </div>
            <h1 class="text-3xl font-black mt-3">{{ $report->species_emoji }} {{ $report->display_name }}</h1>

            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-ink-50 p-3"><dt class="text-ink-500 text-xs font-bold uppercase">Especie</dt><dd class="font-bold">{{ $report->species_label }}</dd></div>
                <div class="rounded-xl bg-ink-50 p-3"><dt class="text-ink-500 text-xs font-bold uppercase">Raza</dt><dd class="font-bold">{{ $report->breed ?: 'No especificada' }}</dd></div>
                <div class="rounded-xl bg-ink-50 p-3"><dt class="text-ink-500 text-xs font-bold uppercase">Color</dt><dd class="font-bold">{{ $report->color_labels ?: '—' }}</dd></div>
                <div class="rounded-xl bg-ink-50 p-3"><dt class="text-ink-500 text-xs font-bold uppercase">Sexo · tamaño</dt>
                    <dd class="font-bold">{{ config('pets.sexes.'.$report->sex, '—') }}{{ $report->size ? ' · '.ucfirst($report->size) : '' }}</dd></div>
                <div class="rounded-xl bg-ink-50 p-3 col-span-2"><dt class="text-ink-500 text-xs font-bold uppercase">{{ $lost ? 'Se perdió' : 'Se encontró' }}</dt>
                    <dd class="font-bold">{{ optional($report->event_date)->translatedFormat('d \d\e F \d\e Y') ?? $report->created_at->format('d/m/Y') }}{{ $report->location_reference ? ' · '.$report->location_reference : '' }}</dd></div>
            </dl>

            <p class="mt-4 text-ink-700 leading-relaxed">{{ $report->description }}</p>
        </div>
    </article>

    <aside class="space-y-4">
        {{-- HU-06: contacto directo sin cuenta --}}
        @if ($report->status === 'activo')
            <div class="bg-white rounded-3xl shadow-soft border border-ink-100 p-6">
                <h2 class="font-extrabold text-lg">{{ $lost ? '¿La has visto?' : '¿Es tu mascota?' }}</h2>
                <p class="text-sm text-ink-500 mt-1">Comunícate directamente con quien publicó el reporte.</p>
                <div class="mt-4 grid gap-2">
                    @if ($report->contact_whatsapp && $report->whatsapp_number)
                        <a href="https://wa.me/{{ $report->whatsapp_number }}?text={{ $waText }}" target="_blank" rel="noopener"
                           class="flex items-center justify-center gap-2 rounded-xl bg-[#25D366] hover:brightness-95 text-white font-extrabold py-3">
                            💬 Escribir por WhatsApp
                        </a>
                    @endif
                    @if ($report->contact_phone)
                        <a href="tel:{{ $report->contact_phone }}" class="flex items-center justify-center gap-2 rounded-xl bg-ink-900 hover:bg-ink-700 text-white font-extrabold py-3">
                            📞 Llamar
                        </a>
                    @endif
                    @if ($report->contact_email)
                        <a href="mailto:{{ $report->contact_email }}?subject={{ rawurlencode('PetRescue: '.$report->display_name) }}"
                           class="flex items-center justify-center gap-2 rounded-xl border border-ink-200 hover:bg-ink-50 font-bold py-3">✉️ Enviar correo</a>
                    @endif
                    <button type="button" data-share="{{ $publicUrl }}" data-title="{{ $report->display_name }}"
                            class="flex items-center justify-center gap-2 rounded-xl border border-ink-200 hover:bg-ink-50 font-bold py-3">🔗 Compartir</button>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-soft border border-ink-100 p-3">
            <div id="report-map" class="h-64 rounded-2xl"></div>
            <a class="block text-center text-sm font-bold text-brand-700 hover:underline mt-2"
               target="_blank" rel="noopener"
               href="https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }}">Abrir en Google Maps</a>
        </div>

        {{-- Gestión sin login --}}
        @if ($canManage && $report->status !== 'cerrado')
            <div class="bg-white rounded-3xl shadow-soft border border-ink-100 p-6">
                <h2 class="font-extrabold">Actualizar el reporte</h2>
                <form action="{{ route('reports.updateStatus', $report->management_token) }}" method="POST" class="mt-3 grid gap-2">
                    @csrf
                    @method('PATCH')
                    @if ($report->status !== 'reunido')
                        <button name="status" value="reunido" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3">🎉 ¡Ya está en casa! Marcar como reunido</button>
                    @endif
                    <button name="status" value="cerrado" class="rounded-xl border border-ink-200 hover:bg-ink-50 font-bold py-3">Cerrar reporte</button>
                </form>
            </div>
        @endif
    </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const lat = {{ (float) $report->latitude }}, lng = {{ (float) $report->longitude }};
    const map = L.map('report-map', { scrollWheelZoom: false }).setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    L.marker([lat, lng], { icon: L.divIcon({ className: '', iconSize: [36, 36], iconAnchor: [18, 36],
        html: '<div class="pin" style="background:{{ $lost ? '#f43f5e' : '#059669' }}"><span>{{ $report->species_emoji }}</span></div>' }) }).addTo(map);

    document.querySelectorAll('[data-copy]').forEach(b => b.addEventListener('click', () => {
        navigator.clipboard.writeText(b.dataset.copy).then(() => { b.textContent = '¡Copiado!'; });
    }));
    document.querySelectorAll('[data-share]').forEach(b => b.addEventListener('click', () => {
        const url = b.dataset.share;
        if (navigator.share) { navigator.share({ title: b.dataset.title, url: url }).catch(() => {}); }
        else { navigator.clipboard.writeText(url).then(() => { b.textContent = '¡Enlace copiado!'; }); }
    }));
})();
</script>
@endpush
