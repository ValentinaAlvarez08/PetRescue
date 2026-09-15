@extends('layouts.app')

@section('title', 'Avisos de reportes cercanos')

@section('content')
    <h1 class="text-2xl font-bold mb-1">🔔 Avísame de reportes cercanos</h1>
    <p class="text-sm text-gray-600 mb-6">
        Sin necesidad de crear una cuenta. Déjanos tu correo y la zona que quieres vigilar,
        y te avisaremos automáticamente cuando alguien reporte una mascota perdida o encontrada cerca.
    </p>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('subscribers.store') }}" method="POST" class="space-y-4 bg-white border rounded-lg p-5">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Nombre</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Correo *</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Radio a vigilar (km)</label>
            <input type="number" step="0.5" min="0.5" max="100" name="radius_km" value="{{ old('radius_km', 5) }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Latitud *</label>
                <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}" required
                       class="w-full border rounded px-3 py-2" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Longitud *</label>
                <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}" required
                       class="w-full border rounded px-3 py-2" readonly>
            </div>
        </div>

        <button type="button" id="btn-location" class="bg-gray-200 hover:bg-gray-300 text-sm px-3 py-2 rounded">
            📍 Usar mi ubicación actual
        </button>

        <div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded">
                Activar avisos
            </button>
        </div>
    </form>

    <script>
        document.getElementById('btn-location').addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('Tu navegador no soporta geolocalización. Puedes escribir las coordenadas manualmente.');
                return;
            }
            navigator.geolocation.getCurrentPosition(function (pos) {
                document.getElementById('latitude').value = pos.coords.latitude;
                document.getElementById('longitude').value = pos.coords.longitude;
            }, function () {
                alert('No se pudo obtener tu ubicación.');
            });
        });
    </script>
@endsection
