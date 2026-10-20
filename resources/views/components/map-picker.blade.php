{{--
    Sprint 2 — HU-14: selector de ubicación en el mapa.
    El usuario marca el punto con un clic (o arrastrando el pin), busca una
    dirección o usa su ubicación actual. Guarda lat/lng en campos ocultos y,
    si existe, completa el campo de "punto de referencia" con la dirección.
--}}
@props([
    'latName' => 'latitude',
    'lngName' => 'longitude',
    'lat' => null,
    'lng' => null,
    'referenceInput' => null, // id del input de referencia a autocompletar
    'color' => '#f97316',
    'emoji' => '📍',
    'height' => 'h-80',
])

@php
    $id = 'map-picker-'.\Illuminate\Support\Str::random(6);
    $center = config('pets.default_center');
    $bounds = config('pets.bounds');
@endphp

<div id="{{ $id }}" class="space-y-3">
    <div class="flex flex-col sm:flex-row gap-2">
        <div class="relative flex-1">
            <input type="search" data-role="search" autocomplete="off"
                   placeholder="Busca una dirección o barrio (ej: Parque Infantil, Pasto)"
                   class="w-full rounded-xl border border-ink-200 bg-white px-4 py-3 pr-24 focus:outline-none focus:ring-2 focus:ring-brand-400">
            <button type="button" data-role="search-btn"
                    class="absolute right-1.5 top-1.5 bottom-1.5 px-3 rounded-lg bg-ink-100 hover:bg-ink-200 text-sm font-bold">Buscar</button>
            <ul data-role="results"
                class="hidden absolute z-[1100] mt-1 w-full bg-white rounded-xl border border-ink-200 shadow-soft max-h-60 overflow-auto text-sm"></ul>
        </div>
        <button type="button" data-role="locate"
                class="rounded-xl border border-ink-200 bg-white hover:bg-ink-100 px-4 py-3 font-bold text-sm whitespace-nowrap">
            🎯 Usar mi ubicación
        </button>
    </div>

    <div class="relative">
        <div data-role="map" class="w-full {{ $height }} rounded-2xl border border-ink-200 z-0"></div>
        <div data-role="hint"
             class="pointer-events-none absolute left-1/2 -translate-x-1/2 bottom-3 z-[500] bg-ink-900/85 text-white text-xs font-semibold px-3 py-2 rounded-full">
            Toca el mapa para marcar el lugar
        </div>
    </div>

    <p data-role="status" class="text-sm text-ink-500"></p>

    <input type="hidden" name="{{ $latName }}" data-role="lat" value="{{ $lat }}">
    <input type="hidden" name="{{ $lngName }}" data-role="lng" value="{{ $lng }}">
</div>

@push('scripts')
<script>
(function () {
    const root = document.getElementById(@json($id));
    const $ = (role) => root.querySelector('[data-role="' + role + '"]');
    const latInput = $('lat'), lngInput = $('lng'), status = $('status'), hint = $('hint');
    const refInput = @json($referenceInput) ? document.getElementById(@json($referenceInput)) : null;
    const bounds = @json($bounds);
    const center = @json($center);

    const map = L.map($('map'), { scrollWheelZoom: false }).setView([center.lat, center.lng], center.zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const icon = L.divIcon({
        className: '',
        html: '<div class="pin" style="background:{{ $color }}"><span>{{ $emoji }}</span></div>',
        iconSize: [36, 36], iconAnchor: [18, 36],
    });
    let marker = null;

    function inColombia(lat, lng) {
        return lat >= bounds.lat_min && lat <= bounds.lat_max && lng >= bounds.lng_min && lng <= bounds.lng_max;
    }

    function setPoint(lat, lng, opts) {
        opts = opts || {};
        if (!inColombia(lat, lng)) {
            status.textContent = '⚠️ El punto debe estar dentro de Colombia.';
            status.className = 'text-sm text-rose-600 font-semibold';
            return;
        }
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        if (!marker) {
            marker = L.marker([lat, lng], { icon: icon, draggable: true }).addTo(map);
            marker.on('dragend', function () {
                const p = marker.getLatLng();
                setPoint(p.lat, p.lng, { reverse: true });
            });
        } else {
            marker.setLatLng([lat, lng]);
        }
        if (opts.fly) map.setView([lat, lng], 16);
        hint.classList.add('hidden');
        status.textContent = '✅ Lugar marcado. Puedes arrastrar el pin para ajustarlo.';
        status.className = 'text-sm text-emerald-700 font-semibold';
        root.dispatchEvent(new CustomEvent('location:set', { bubbles: true }));
        if (opts.reverse) reverseGeocode(lat, lng);
    }

    function reverseGeocode(lat, lng) {
        if (!refInput || refInput.dataset.touched === '1') return;
        fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=es&zoom=17&lat=' + lat + '&lon=' + lng)
            .then(r => r.json())
            .then(d => {
                if (!d || !d.address) return;
                const a = d.address;
                const parts = [a.road, a.neighbourhood || a.suburb, a.city || a.town || a.village].filter(Boolean);
                if (parts.length && refInput.dataset.touched !== '1') refInput.value = parts.join(', ').slice(0, 150);
            })
            .catch(() => {});
    }
    if (refInput) refInput.addEventListener('input', () => { refInput.dataset.touched = '1'; });

    map.on('click', e => setPoint(e.latlng.lat, e.latlng.lng, { reverse: true }));

    $('locate').addEventListener('click', function () {
        if (!navigator.geolocation) {
            status.textContent = 'Tu navegador no permite obtener la ubicación. Marca el punto en el mapa.';
            return;
        }
        status.textContent = 'Buscando tu ubicación...';
        status.className = 'text-sm text-ink-500';
        navigator.geolocation.getCurrentPosition(
            p => setPoint(p.coords.latitude, p.coords.longitude, { fly: true, reverse: true }),
            () => { status.textContent = 'No pudimos obtener tu ubicación. Marca el punto tocando el mapa.'; },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    const search = $('search'), results = $('results');
    function doSearch() {
        const q = search.value.trim();
        if (q.length < 3) return;
        results.innerHTML = '<li class="px-4 py-3 text-ink-500">Buscando...</li>';
        results.classList.remove('hidden');
        fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&countrycodes=co&accept-language=es&q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(items => {
                if (!items.length) {
                    results.innerHTML = '<li class="px-4 py-3 text-ink-500">Sin resultados. Prueba con otra dirección o marca el punto en el mapa.</li>';
                    return;
                }
                results.innerHTML = '';
                items.forEach(it => {
                    const li = document.createElement('li');
                    li.className = 'px-4 py-3 hover:bg-brand-50 cursor-pointer border-b border-ink-100 last:border-0';
                    li.textContent = it.display_name;
                    li.addEventListener('click', () => {
                        results.classList.add('hidden');
                        if (refInput && refInput.dataset.touched !== '1') refInput.value = it.display_name.split(',').slice(0, 3).join(',').slice(0, 150);
                        setPoint(parseFloat(it.lat), parseFloat(it.lon), { fly: true });
                    });
                    results.appendChild(li);
                });
            })
            .catch(() => { results.innerHTML = '<li class="px-4 py-3 text-rose-600">No se pudo buscar. Marca el punto en el mapa.</li>'; });
    }
    $('search-btn').addEventListener('click', doSearch);
    search.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); doSearch(); } });
    document.addEventListener('click', e => { if (!root.contains(e.target)) results.classList.add('hidden'); });

    // Si el formulario vuelve con errores, se conserva el punto elegido.
    if (latInput.value && lngInput.value) {
        setPoint(parseFloat(latInput.value), parseFloat(lngInput.value), { fly: true });
    }

    // Leaflet necesita recalcular el tamaño cuando el mapa estaba oculto (pasos del formulario).
    root.addEventListener('map:refresh', () => setTimeout(() => map.invalidateSize(), 50));
})();
</script>
@endpush
