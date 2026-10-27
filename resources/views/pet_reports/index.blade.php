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

{{-- Sprint 3 — HU-04: filtros por tipo, especie, color y radio. Van por GET para que la URL se pueda compartir. --}}
@php
    $select = 'w-full rounded-xl border border-ink-200 bg-white px-3 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-300';
    $hasLocation = $lat !== null && $lng !== null;
@endphp
<form id="report-filters" action="{{ route('reports.index') }}" method="GET"
      class="rounded-3xl bg-white border border-ink-100 shadow-soft p-4 mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-[repeat(4,1fr)_auto] items-end">
    <div>
        <label for="f-type" class="block text-xs font-bold text-ink-500 mb-1">Tipo</label>
        <select id="f-type" name="type" class="{{ $select }}">
            <option value="">Perdidas y encontradas</option>
            <option value="perdida" @selected($filters['type'] === 'perdida')>Perdidas</option>
            <option value="encontrada" @selected($filters['type'] === 'encontrada')>Encontradas</option>
        </select>
    </div>
    <div>
        <label for="f-species" class="block text-xs font-bold text-ink-500 mb-1">Especie</label>
        <select id="f-species" name="species" class="{{ $select }}">
            <option value="">Todas</option>
            @foreach ($speciesCatalog as $key => $sp)
                <option value="{{ $key }}" @selected($filters['species'] === $key)>{{ $sp['emoji'] }} {{ $sp['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="f-color" class="block text-xs font-bold text-ink-500 mb-1">Color</label>
        <select id="f-color" name="color" class="{{ $select }}">
            <option value="">Todos</option>
            @foreach ($colorsCatalog as $key => $c)
                <option value="{{ $key }}" @selected($filters['color'] === $key)>{{ $c['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="f-radius" class="block text-xs font-bold text-ink-500 mb-1">Zona (radio desde tu ubicación)</label>
        <select id="f-radius" name="radius" class="{{ $select }}" @disabled(! $hasLocation) title="{{ $hasLocation ? '' : 'Activa tu ubicación con «Ver cerca de mí»' }}">
            @foreach ($radiusOptions as $km)
                <option value="{{ $km }}" @selected((float) $radius === (float) $km)>A menos de {{ $km }} km</option>
            @endforeach
        </select>
    </div>
    @if ($hasLocation)
        <input type="hidden" name="lat" value="{{ $lat }}">
        <input type="hidden" name="lng" value="{{ $lng }}">
    @endif
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold px-4 py-2.5 text-sm">Filtrar</button>
        @if ($hasFilters)
            <a href="{{ route('reports.index') }}" class="rounded-xl border border-ink-200 hover:bg-ink-100 font-bold px-4 py-2.5 text-sm whitespace-nowrap">Limpiar</a>
        @endif
    </div>
</form>

<div class="flex flex-wrap items-center gap-2 mb-4">
    <span id="radar-status" class="text-sm text-ink-500">
        @if ($hasLocation)
            Mostrando reportes a menos de {{ rtrim(rtrim(number_format($radius, 1), '0'), '.') }} km de ti, del más cercano al más lejano.
        @else
            Activa «Ver cerca de mí» para buscar por zona.
        @endif
    </span>
    <span class="text-sm font-bold text-ink-700 ml-auto">{{ $reports->count() }} {{ $reports->count() === 1 ? 'reporte' : 'reportes' }}</span>
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
            @if ($hasFilters)
                {{-- HU-04: sin resultados con los filtros elegidos --}}
                <p class="font-bold mt-2">No hay reportes con esos filtros</p>
                <p class="text-sm text-ink-500 mt-1">Prueba con menos filtros o un radio más grande.</p>
                <a href="{{ route('reports.index') }}" class="inline-block mt-4 rounded-xl bg-ink-900 hover:bg-ink-700 text-white font-bold px-5 py-3 text-sm">Limpiar filtros</a>
            @else
                <p class="font-bold mt-2">No hay reportes activos por ahora.</p>
            @endif
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
            // HU-04: conserva los filtros elegidos (aunque aún no se haya pulsado "Filtrar").
            new FormData(document.getElementById('report-filters')).forEach((v, k) => {
                if (k !== 'lat' && k !== 'lng') { v ? url.searchParams.set(k, v) : url.searchParams.delete(k); }
            });
            if (!url.searchParams.has('radius')) url.searchParams.set('radius', 5);
            window.location.href = url.toString();
        }, () => { status.textContent = 'No se pudo obtener tu ubicación.'; });
    });
})();
</script>
@endpush
