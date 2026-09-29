@extends('layouts.app')

@section('title', 'Reportes de mascotas — PetRescue')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
    <div>
        <h1 class="text-3xl font-black tracking-tight">Mascotas perdidas y encontradas</h1>
        <p class="text-ink-500 mt-1">Casos activos de la comunidad. Activa tu ubicación para ver los más cercanos.</p>
    </div>
    <button id="btn-radar" class="rounded-xl bg-ink-900 hover:bg-ink-700 text-white font-bold px-4 py-3 text-sm whitespace-nowrap">🎯 Ver cerca de mí</button>
</div>

@php
    $tabs = ['' => 'Todos', 'perdida' => 'Perdidas', 'encontrada' => 'Encontradas'];
@endphp
<div class="flex flex-wrap items-center gap-2 mb-4">
    @foreach ($tabs as $key => $label)
        <a href="{{ route('reports.index', array_filter(['type' => $key, 'lat' => $lat, 'lng' => $lng])) }}"
           class="px-4 py-2 rounded-full text-sm font-bold border {{ (string) ($type ?? '') === (string) $key ? 'bg-ink-900 text-white border-ink-900' : 'bg-white border-ink-200 hover:bg-ink-100' }}">{{ $label }}</a>
    @endforeach
    <span id="radar-status" class="text-sm text-ink-500 ml-2">
        @if ($lat && $lng) Mostrando reportes a menos de {{ $radius }} km de ti. @endif
    </span>
</div>

<div class="relative mb-8">
    <div id="map" class="w-full h-96 rounded-3xl border border-ink-100 shadow-soft z-0"></div>
    <div class="absolute top-3 right-3 z-[500] bg-white/95 rounded-xl shadow-soft px-3 py-2 text-xs font-bold space-y-1">
        <p><span class="inline-block w-3 h-3 rounded-full bg-rose-500 align-middle mr-1"></span>Perdida</p>
        <p><span class="inline-block w-3 h-3 rounded-full bg-emerald-600 align-middle mr-1"></span>Encontrada</p>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($reports as $report)
        @include('pet_reports._card', ['report' => $report])
    @empty
        <div class="col-span-full text-center rounded-3xl border-2 border-dashed border-ink-200 p-10">
            <p class="text-4xl">🐾</p>
            <p class="font-bold mt-2">No hay reportes activos {{ $lat ? 'en tu zona' : 'por ahora' }}.</p>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
(function () {
    const reports = @json($mapReports);
    const c = @json(config('pets.default_center'));
    const map = L.map('map', { scrollWheelZoom: false }).setView([c.lat, c.lng], c.zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));

    const markers = reports.map(r => {
        const color = r.type === 'perdida' ? '#f43f5e' : '#059669';
        const m = L.marker([r.lat, r.lng], { icon: L.divIcon({ className: '', iconSize: [36, 36], iconAnchor: [18, 36], popupAnchor: [0, -32],
            html: '<div class="pin" style="background:' + color + '"><span>' + r.emoji + '</span></div>' }) }).addTo(map);
        m.bindPopup(
            '<div style="width:190px">' +
            (r.photo_url ? '<img src="' + esc(r.photo_url) + '" style="width:100%;height:110px;object-fit:cover;border-radius:10px;margin-bottom:6px">' : '') +
            '<strong>' + esc(r.pet_name) + '</strong><br><span style="color:#78716c">' + esc(r.species) + ' · ' + (r.type === 'perdida' ? 'Perdida' : 'Encontrada') + '</span><br>' +
            '<a href="' + esc(r.url) + '" style="font-weight:700;color:#c2410c">Ver reporte →</a></div>'
        );
        return m;
    });
    if (markers.length) map.fitBounds(L.featureGroup(markers).getBounds().pad(0.2), { maxZoom: 15 });

    document.getElementById('btn-radar').addEventListener('click', function () {
        const status = document.getElementById('radar-status');
        if (!navigator.geolocation) { status.textContent = 'Tu navegador no soporta geolocalización.'; return; }
        status.textContent = 'Buscando tu ubicación...';
        navigator.geolocation.getCurrentPosition(pos => {
            const url = new URL(window.location.href);
            url.searchParams.set('lat', pos.coords.latitude);
            url.searchParams.set('lng', pos.coords.longitude);
            window.location.href = url.toString();
        }, () => { status.textContent = 'No se pudo obtener tu ubicación.'; });
    });
})();
</script>
@endpush
