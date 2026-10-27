@extends('layouts.app')

@section('title', 'Alertas de reportes por zona — PetRescue')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-black tracking-tight">🔔 Alertas de las zonas que te importan</h1>
    <p class="text-ink-500 mt-2 mb-6">
        Sin crear cuenta. Marca la zona que quieres vigilar y te escribiremos cuando alguien reporte una mascota perdida o encontrada ahí.
    </p>

    {{-- HU-11 (CID 3): varias zonas con el mismo correo --}}
    <div class="mb-6 grid sm:grid-cols-3 gap-3 text-sm">
        <div class="rounded-2xl bg-white border border-ink-100 shadow-soft p-4">
            <p class="text-xl">🏠 🏢 🌄</p>
            <p class="font-extrabold mt-1">Varias zonas</p>
            <p class="text-ink-500">Casa, trabajo, finca... Usa el mismo correo para agregar hasta {{ $alerts['max_zones'] }}.</p>
        </div>
        <div class="rounded-2xl bg-white border border-ink-100 shadow-soft p-4">
            <p class="text-xl">📬</p>
            <p class="font-extrabold mt-1">Un solo correo por reporte</p>
            <p class="text-ink-500">Si un reporte cae en dos de tus zonas, te avisamos una sola vez.</p>
        </div>
        <div class="rounded-2xl bg-white border border-ink-100 shadow-soft p-4">
            <p class="text-xl">🔗</p>
            <p class="font-extrabold mt-1">Todo desde un enlace</p>
            <p class="text-ink-500">Te enviamos un enlace privado para cambiar el radio, pausar o borrar zonas.</p>
        </div>
    </div>

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

        @include('subscribers._zone_fields')

        <button type="submit" class="w-full sm:w-auto rounded-xl bg-ink-900 hover:bg-ink-700 text-white font-extrabold px-6 py-3">
            {{ session('status') ? 'Agregar otra zona' : 'Activar alertas' }}
        </button>
    </form>
</div>
@endsection
