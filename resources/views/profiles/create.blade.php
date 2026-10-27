@extends('layouts.app')

@section('title', 'Crea el perfil digital de tu mascota — PetRescue')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Sprint 4 — HU-10 --}}
    <div class="mb-6">
        <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Perfil digital</p>
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight mt-1">Su cédula digital, lista por si algún día la necesitas</h1>
        <p class="text-ink-500 mt-2">
            Guarda la foto, las señas y los datos de salud de tu mascota. Te damos un código QR para la placa del collar:
            quien la encuentre verá cómo contactarte. Y si se pierde, creas el reporte con un clic. Sin crear cuenta.
        </p>
    </div>

    <form action="{{ route('profiles.store') }}" method="POST" enctype="multipart/form-data" novalidate
          class="bg-white rounded-3xl shadow-soft border border-ink-100 p-5 sm:p-8 space-y-8">
        @csrf
        @include('profiles._form', ['profile' => null])
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 rounded-xl font-extrabold text-white bg-brand-500 hover:bg-brand-600">Crear perfil</button>
        </div>
    </form>
</div>
@endsection
