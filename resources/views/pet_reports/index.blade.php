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
