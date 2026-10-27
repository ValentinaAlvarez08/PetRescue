@extends('layouts.app')

@section('title', 'Registrar mi negocio en el directorio — PetRescue')

@php
    $input = 'w-full rounded-xl border border-ink-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400';
    $err = fn ($field) => $errors->has($field) ? ' border-rose-400 bg-rose-50' : '';
@endphp

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('directory.index') }}" class="text-sm font-bold text-ink-500 hover:text-ink-900">← Directorio</a>

    {{-- Sprint 4 — HU-19 --}}
    <div class="mt-4 mb-6">
        <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Directorio</p>
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight mt-1">Registra tu negocio para mascotas</h1>
        <p class="text-ink-500 mt-2">
            Es gratis. Revisamos cada solicitud para confirmar que sea un servicio para mascotas y te avisamos por correo
            cuando aparezca en el directorio y en el mapa.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">
            <p class="font-bold mb-1">Revisa estos datos:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('directory.store') }}" method="POST" novalidate
          class="bg-white rounded-3xl shadow-soft border border-ink-100 p-5 sm:p-8 space-y-8">
        @csrf

        <section class="space-y-5">
            <h2 class="text-lg font-extrabold">El negocio</h2>
            <div>
                <p class="block text-sm font-bold mb-2">¿A qué se dedica? <span class="text-rose-500">*</span></p>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                    @foreach ($categories as $key => $cat)
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="{{ $key }}" class="peer sr-only" @checked(old('category') === $key)>
                            <span class="flex flex-col items-center justify-center gap-1 h-24 rounded-2xl border-2 border-ink-100 bg-ink-50 peer-checked:border-brand-500 peer-checked:bg-brand-50 text-center px-1">
                                <span class="text-2xl">{{ $cat['emoji'] }}</span>
                                <span class="text-xs font-bold leading-tight">{{ $cat['short'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="name" class="block text-sm font-bold mb-1">Nombre del negocio <span class="text-rose-500">*</span></label>
                <input id="name" name="name" type="text" maxlength="120" value="{{ old('name') }}" class="{{ $input }}{{ $err('name') }}">
            </div>
            <div>
                <label for="description" class="block text-sm font-bold mb-1">¿Qué ofrece? <span class="text-rose-500">*</span></label>
                <textarea id="description" name="description" rows="3" maxlength="500"
                          placeholder="Ej: veterinaria con consulta general, vacunación y cirugías. Atendemos perros, gatos y conejos."
                          class="{{ $input }}{{ $err('description') }}">{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="services" class="block text-sm font-bold mb-1">Servicios principales <span class="font-normal text-ink-500">(separados por coma)</span></label>
                <input id="services" name="services" type="text" maxlength="300" value="{{ old('services') }}" placeholder="Ej: Vacunas, Cirugía, Urgencias 24 h"
                       class="{{ $input }}{{ $err('services') }}">
            </div>
        </section>

        <section class="space-y-5">
            <h2 class="text-lg font-extrabold">Ubicación y contacto</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="address" class="block text-sm font-bold mb-1">Dirección <span class="text-rose-500">*</span></label>
                    <input id="address" name="address" type="text" maxlength="200" value="{{ old('address') }}" placeholder="Ej: Calle 18 # 25-30, Pasto"
                           class="{{ $input }}{{ $err('address') }}">
                </div>
                <div>
                    <label for="schedule" class="block text-sm font-bold mb-1">Horario <span class="font-normal text-ink-500">(opcional)</span></label>
                    <input id="schedule" name="schedule" type="text" maxlength="150" value="{{ old('schedule') }}" placeholder="Ej: Lun a sáb 8:00 a. m. – 6:00 p. m."
                           class="{{ $input }}">
                </div>
            </div>
            <div>
                <p class="block text-sm font-bold mb-1">Marca el negocio en el mapa <span class="text-rose-500">*</span></p>
                <x-map-picker :lat="old('latitude')" :lng="old('longitude')" emoji="🏪" height="h-72" />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-bold mb-1">Teléfono <span class="text-rose-500">*</span></label>
                    <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="14" value="{{ old('phone') }}" placeholder="300 123 4567 o 602 7xx xxxx"
                           class="{{ $input }}{{ $err('phone') }}">
                    <label class="inline-flex items-center gap-2 mt-2 text-sm">
                        <input type="checkbox" name="has_whatsapp" value="1" class="w-4 h-4 accent-emerald-600" @checked(old('has_whatsapp'))>
                        Este número tiene WhatsApp
                    </label>
                </div>
                <div>
                    <label for="email" class="block text-sm font-bold mb-1">Correo del negocio <span class="font-normal text-ink-500">(opcional)</span></label>
                    <input id="email" name="email" type="email" maxlength="150" value="{{ old('email') }}" class="{{ $input }}{{ $err('email') }}">
                </div>
            </div>
        </section>

        <section class="space-y-5">
            <h2 class="text-lg font-extrabold">¿Quién hace la solicitud?</h2>
            <p class="text-sm text-ink-500 -mt-3">No se publica: solo lo usamos para contarte cómo va la revisión.</p>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="requester_name" class="block text-sm font-bold mb-1">Tu nombre <span class="text-rose-500">*</span></label>
                    <input id="requester_name" name="requester_name" type="text" maxlength="80" value="{{ old('requester_name') }}" class="{{ $input }}{{ $err('requester_name') }}">
                </div>
                <div>
                    <label for="requester_email" class="block text-sm font-bold mb-1">Tu correo <span class="text-rose-500">*</span></label>
                    <input id="requester_email" name="requester_email" type="email" maxlength="150" value="{{ old('requester_email') }}" class="{{ $input }}{{ $err('requester_email') }}">
                </div>
            </div>
            <label class="flex items-start gap-2 text-sm">
                <input type="checkbox" name="authorized" value="1" class="mt-1 w-4 h-4 accent-brand-600" @checked(old('authorized'))>
                Soy el dueño o tengo autorización para registrar este negocio, y los datos son reales.
            </label>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 rounded-xl font-extrabold text-white bg-brand-500 hover:bg-brand-600">Enviar solicitud</button>
        </div>
    </form>
</div>
@endsection
