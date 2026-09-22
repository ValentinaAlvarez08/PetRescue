@extends('layouts.app')

@section('title', $type === 'perdida' ? 'Reportar mascota perdida' : 'Reportar mascota encontrada')

@section('content')
    <h1 class="text-2xl font-bold mb-4">
        {{ $type === 'perdida' ? 'Reportar mascota perdida' : 'Reportar mascota encontrada' }}
    </h1>

    <p class="text-sm text-gray-600 mb-6">
        No necesitas crear una cuenta. Al enviar el formulario recibirás un enlace privado para
        darle seguimiento a tu reporte — guárdalo, es tu único acceso.
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

    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 bg-white border rounded-lg p-5">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        <div>
            <label class="block text-sm font-medium mb-1">Nombre de la mascota (si lo conoces)</label>
            <input type="text" name="pet_name" value="{{ old('pet_name') }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Especie *</label>
                <input type="text" name="species" value="{{ old('species') }}" required
                       placeholder="Perro, gato..."
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Color *</label>
                <input type="text" name="color" value="{{ old('color') }}" required
                       class="w-full border rounded px-3 py-2">
            </div>
        </div>

        @if ($type === 'perdida')
            <div>
                <label class="block text-sm font-medium mb-1">Raza *</label>
                <input type="text" name="breed" value="{{ old('breed') }}" required
                       class="w-full border rounded px-3 py-2">
            </div>
        @else
            <div>
                <label class="block text-sm font-medium mb-1">Tamaño *</label>
                <select name="size" required class="w-full border rounded px-3 py-2">
                    <option value="">Selecciona...</option>
                    @foreach (['pequeño', 'mediano', 'grande'] as $option)
                        <option value="{{ $option }}" @selected(old('size') === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Descripción *</label>
            <textarea name="description" rows="3" required
                      class="w-full border rounded px-3 py-2"
                      placeholder="Señas particulares, comportamiento...">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Foto *</label>
            <input type="file" name="photo" accept="image/*" required class="w-full border rounded px-3 py-2">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Teléfono / WhatsApp</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Correo electrónico</label>
                <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                       class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <p class="text-xs text-gray-500 -mt-2">Deja al menos un medio de contacto (teléfono o correo).</p>

        <div>
            <label class="block text-sm font-medium mb-1">Punto de referencia</label>
            <input type="text" name="location_reference" value="{{ old('location_reference') }}"
                   placeholder="Ej: Parque principal, barrio San José..."
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

        <button type="button" id="btn-location"
                class="bg-gray-200 hover:bg-gray-300 text-sm px-3 py-2 rounded">
            📍 Usar mi ubicación actual
        </button>

        <div>
            <button type="submit"
                    class="{{ $type === 'perdida' ? 'bg-red-500 hover:bg-red-600' : 'bg-green-600 hover:bg-green-700' }} text-white px-5 py-2 rounded">
                Publicar reporte
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
