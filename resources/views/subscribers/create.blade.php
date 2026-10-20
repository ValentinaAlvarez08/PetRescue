@extends('layouts.app')

@section('title', 'Avisos de reportes cercanos — PetRescue')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-black tracking-tight">🔔 Avísame de reportes cercanos</h1>
    <p class="text-ink-500 mt-2 mb-6">
        Sin crear cuenta. Marca la zona que quieres vigilar y te escribiremos cuando alguien reporte una mascota perdida o encontrada cerca.
    </p>

    @if ($errors->any())
        <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('subscribers.store') }}" method="POST" class="bg-white rounded-3xl shadow-soft border border-ink-100 p-5 sm:p-8 space-y-5">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-bold mb-1">Nombre <span class="font-normal text-ink-500">(opcional)</span></label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-ink-200 px-4 py-3">
            </div>
            <div>
                <label for="email" class="block text-sm font-bold mb-1">Correo <span class="text-rose-500">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-ink-200 px-4 py-3">
            </div>
        </div>

        <div>
            <p class="block text-sm font-bold mb-1">Zona a vigilar <span class="text-rose-500">*</span></p>
            <x-map-picker :lat="old('latitude')" :lng="old('longitude')" color="#f97316" emoji="🏠" height="h-72" />
        </div>

        <div>
            <label for="radius_km" class="block text-sm font-bold mb-1">Radio: <span id="radius-label">{{ old('radius_km', 5) }}</span> km</label>
            <input id="radius_km" type="range" min="1" max="20" step="1" name="radius_km" value="{{ old('radius_km', 5) }}" class="w-full accent-brand-500"
                   oninput="document.getElementById('radius-label').textContent = this.value">
        </div>

        <button type="submit" class="w-full sm:w-auto rounded-xl bg-ink-900 hover:bg-ink-700 text-white font-extrabold px-6 py-3">Activar avisos</button>
    </form>
</div>
@endsection
