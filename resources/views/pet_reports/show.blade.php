@extends('layouts.app')

@section('title', 'Detalle del reporte')

@section('content')
    <a href="{{ route('reports.index') }}" class="text-sm text-gray-500">&larr; Volver al listado</a>

    <div class="bg-white border rounded-lg overflow-hidden mt-4">
        @if ($report->photo_path)
            <img src="{{ Storage::url($report->photo_path) }}" class="w-full max-h-96 object-cover">
        @endif

        <div class="p-5">
            <span class="text-xs font-semibold uppercase {{ $report->type === 'perdida' ? 'text-red-600' : 'text-green-700' }}">
                {{ $report->type === 'perdida' ? 'Mascota perdida' : 'Mascota encontrada' }}
                — estado: {{ ucfirst($report->status) }}
            </span>

            <h1 class="text-xl font-bold mt-1">{{ $report->pet_name ?: 'Mascota sin nombre' }}</h1>
            <p class="text-gray-700 mt-2">{{ $report->description }}</p>

            @if ($report->location_reference)
                <p class="text-sm text-gray-500 mt-2">📍 {{ $report->location_reference }}</p>
            @endif

            <p class="text-sm text-gray-500 mt-1">📞 Contacto: {{ $report->contact_phone }}</p>
        </div>
    </div>

    {{-- Gestión sin login: quien tiene el enlace puede actualizar el estado --}}
    @if ($canManage && $report->status !== 'cerrado')
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800 mb-3">
                Este es tu enlace privado de gestión. Guárdalo — es la única forma de actualizar este reporte.
            </p>
            <form action="{{ route('reports.updateStatus', $report->management_token) }}" method="POST" class="flex gap-2">
                @csrf
                @method('PATCH')
                @if ($report->status !== 'reunido')
                    <button name="status" value="reunido"
                            class="bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-2 rounded">
                        ✅ Marcar como reunido
                    </button>
                @endif
                <button name="status" value="cerrado"
                        class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-3 py-2 rounded">
                    Cerrar reporte
                </button>
            </form>
        </div>
    @endif
@endsection
