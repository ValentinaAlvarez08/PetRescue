@extends('layouts.app')

@section('title', 'PetRescue — Mascotas perdidas y encontradas')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-1">Reportes de mascotas</h1>
        <p class="text-gray-600 text-sm">
            Sin necesidad de crear una cuenta. Activa tu ubicación para ver los casos más cercanos a ti.
        </p>
    </div>

    {{-- HU3: radar de mascotas cercanas --}}
    <div class="mb-6 bg-white border rounded-lg p-4">
        <button id="btn-radar" class="bg-gray-800 text-white px-4 py-2 rounded text-sm">
            📍 Ver reportes cerca de mí
        </button>
        <span id="radar-status" class="text-sm text-gray-500 ml-2"></span>
    </div>

    {{-- HU3: mapa con los reportes activos --}}
    @if (config('services.google_maps.key'))
        <div class="relative mb-6">
            <div id="map" class="w-full h-96 rounded-lg border"></div>
            <div id="map-empty" class="hidden absolute inset-0 flex items-center justify-center bg-white/90 text-gray-600 text-sm rounded-lg px-4 text-center">
                No hay reportes cercanos en la zona visible del mapa.
            </div>
        </div>

        <script>
            const petReportsForMap = @json($mapReports);

            function initPetMap() {
                const mapEl = document.getElementById('map');
                const emptyEl = document.getElementById('map-empty');

                if (!petReportsForMap.length) {
                    new google.maps.Map(mapEl, { center: { lat: 4.6097, lng: -74.0817 }, zoom: 6 });
                    emptyEl.classList.remove('hidden');
                    return;
                }

                const bounds = new google.maps.LatLngBounds();
                const map = new google.maps.Map(mapEl, { zoom: 12 });
                const infoWindow = new google.maps.InfoWindow();
                const markers = [];

                petReportsForMap.forEach(function (report) {
                    const position = { lat: report.lat, lng: report.lng };
                    bounds.extend(position);

                    const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: report.pet_name || report.species,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 8,
                            fillColor: report.type === 'perdida' ? '#ef4444' : '#16a34a',
                            fillOpacity: 1,
                            strokeWeight: 1,
                            strokeColor: '#ffffff',
                        },
                    });
                    markers.push(marker);

                    marker.addListener('click', function () {
                        const photoHtml = report.photo_url
                            ? '<img src="' + report.photo_url + '" style="width:100%;max-height:120px;object-fit:cover;border-radius:4px;margin-bottom:6px;">'
                            : '';
                        infoWindow.setContent(
                            '<div style="max-width:200px;">' + photoHtml +
                            '<strong>' + (report.pet_name || 'Mascota sin nombre') + '</strong><br>' +
                            report.species + ' · ' + report.status + '<br>' +
                            '<a href="' + report.url + '" style="color:#2563eb;">Ver reporte</a>' +
                            '</div>'
                        );
                        infoWindow.open(map, marker);
                    });
                });

                map.fitBounds(bounds);

                map.addListener('idle', function () {
                    const mapBounds = map.getBounds();
                    if (!mapBounds) {
                        return;
                    }
                    const anyVisible = markers.some(function (marker) {
                        return mapBounds.contains(marker.getPosition());
                    });
                    emptyEl.classList.toggle('hidden', anyVisible);
                });
            }
            window.initPetMap = initPetMap;
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initPetMap" async defer></script>
    @else
        <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm rounded-lg p-4">
            El mapa no está disponible: falta configurar <code>GOOGLE_MAPS_API_KEY</code> en el archivo <code>.env</code>.
        </div>
    @endif

    <div id="reports-list" class="grid gap-4 sm:grid-cols-2">
        @forelse ($reports as $report)
            <a href="{{ route('reports.show', $report->management_token) }}"
               class="block bg-white border rounded-lg overflow-hidden hover:shadow">
                @if ($report->photo_path)
                    <img src="{{ Storage::url($report->photo_path) }}" class="w-full h-40 object-cover">
                @endif
                <div class="p-3">
                    <span class="text-xs font-semibold uppercase {{ $report->type === 'perdida' ? 'text-red-600' : 'text-green-700' }}">
                        {{ $report->type === 'perdida' ? 'Perdida' : 'Encontrada' }}
                    </span>
                    <h2 class="font-semibold">{{ $report->pet_name ?: 'Mascota sin nombre' }}</h2>
                    <p class="text-sm text-gray-600 line-clamp-2">{{ $report->description }}</p>
                    @isset($report->distance_km)
                        <p class="text-xs text-gray-400 mt-1">a {{ number_format($report->distance_km, 1) }} km</p>
                    @endisset
                </div>
            </a>
        @empty
            <p class="text-gray-500 col-span-2">Todavía no hay reportes activos.</p>
        @endforelse
    </div>

    <script>
        document.getElementById('btn-radar').addEventListener('click', function () {
            const status = document.getElementById('radar-status');
            if (!navigator.geolocation) {
                status.textContent = 'Tu navegador no soporta geolocalización.';
                return;
            }
            status.textContent = 'Buscando tu ubicación...';
            navigator.geolocation.getCurrentPosition(function (pos) {
                const { latitude, longitude } = pos.coords;
                const url = new URL(window.location.href);
                url.searchParams.set('lat', latitude);
                url.searchParams.set('lng', longitude);
                window.location.href = url.toString();
            }, function () {
                status.textContent = 'No se pudo obtener tu ubicación.';
            });
        });
    </script>
@endsection
