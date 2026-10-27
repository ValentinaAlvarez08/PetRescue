@extends('layouts.app')

{{-- Sprint 4 — HU-07: editar el reporte con el enlace privado (sin login) --}}
@php
    $lost = $report->type === 'perdida';
    $accent = $lost ? 'rose' : 'emerald';
    $input = 'w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400';
    $breeds = $speciesInfo['breeds'] ?? [];
    if (! $lost) {
        array_unshift($breeds, config('pets.unknown_breed'));
    }
    $selectedBreed = old('breed', $report->breed ?? ($lost ? null : config('pets.unknown_breed')));
    $selectedColors = old('colors', $report->color_keys);
    $minDate = now()->subDays(config('pets.max_days_ago'));
    if ($report->event_date && $report->event_date->lt($minDate)) {
        $minDate = $report->event_date;
    }
@endphp

@section('title', 'Editar reporte — PetRescue')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('reports.manage', $report->management_token) }}" class="text-sm font-bold text-ink-500 hover:text-ink-900">← Volver a mi reporte</a>

    <div class="mt-4 mb-6">
        <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full
                     {{ $lost ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
            {{ $lost ? 'Mascota perdida' : 'Mascota encontrada' }} · {{ $report->species_emoji }} {{ $report->species_label }}
        </span>
        <h1 class="text-3xl font-black mt-3 tracking-tight">Editar el reporte</h1>
        <p class="text-ink-500 mt-1">Actualiza lo que haya cambiado: una foto mejor, otro lugar donde la vieron o un nuevo número de contacto.
            El tipo de reporte y la especie no se pueden cambiar.</p>
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

    <form action="{{ route('reports.update', $report->management_token) }}" method="POST" enctype="multipart/form-data" novalidate
          class="bg-white rounded-3xl shadow-soft border border-ink-100 divide-y divide-ink-100">
        @csrf
        @method('PATCH')

        {{-- La mascota --}}
        <section class="p-5 sm:p-8 space-y-6">
            <h2 class="text-lg font-extrabold">La mascota</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="breed" class="block text-sm font-bold mb-1">
                        Raza {!! $lost ? '<span class="text-rose-500">*</span>' : '<span class="font-normal text-ink-500">(opcional)</span>' !!}
                    </label>
                    <select id="breed" name="breed" class="{{ $input }} bg-white">
                        <option value="">Selecciona...</option>
                        @foreach ($breeds as $breed)
                            <option value="{{ $breed }}" @selected($selectedBreed === $breed)>{{ $breed }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="pet_name" class="block text-sm font-bold mb-1">
                        {{ $lost ? 'Nombre' : 'Nombre en la placa' }} <span class="font-normal text-ink-500">(opcional)</span>
                    </label>
                    <input id="pet_name" type="text" name="pet_name" value="{{ old('pet_name', $report->pet_name) }}" maxlength="40" class="{{ $input }}">
                </div>
            </div>

            <div>
                <p class="block text-sm font-bold mb-2">Sexo <span class="text-rose-500">*</span></p>
                <div class="inline-flex rounded-xl bg-ink-100 p-1 gap-1">
                    @foreach ($sexes as $key => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="sex" value="{{ $key }}" class="peer sr-only" @checked(old('sex', $report->sex) === $key)>
                            <span class="block px-4 py-2 rounded-lg text-sm font-bold text-ink-500 peer-checked:bg-white peer-checked:text-ink-900 peer-checked:shadow">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            @if ($speciesInfo['uses_size'] ?? false)
                <div>
                    <p class="block text-sm font-bold mb-2">Tamaño <span class="text-rose-500">*</span></p>
                    <div class="grid sm:grid-cols-3 gap-2">
                        @foreach ($sizes as $key => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="size" value="{{ $key }}" class="peer sr-only" @checked(old('size', $report->size) === $key)>
                                <span class="block rounded-xl border-2 border-ink-100 px-4 py-3 text-sm font-bold peer-checked:border-{{ $accent }}-500 peer-checked:bg-{{ $accent }}-50">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <p class="block text-sm font-bold mb-1">Colores principales <span class="text-rose-500">*</span></p>
                <p class="text-xs text-ink-500 mb-2">Elige hasta {{ config('pets.max_colors') }}.</p>
                <div id="color-chips" class="flex flex-wrap gap-2">
                    @foreach ($colors as $key => $color)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="colors[]" value="{{ $key }}" class="peer sr-only" @checked(in_array($key, $selectedColors, true))>
                            <span class="inline-flex items-center gap-2 rounded-full border-2 border-ink-100 bg-white pl-1.5 pr-3 py-1 text-sm font-semibold
                                         peer-checked:border-ink-900 peer-checked:bg-ink-900 peer-checked:text-white">
                                <span class="w-5 h-5 rounded-full border border-ink-200" style="background: {{ $color['hex'] }}"></span>
                                {{ $color['label'] }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Dónde y cuándo --}}
        <section class="p-5 sm:p-8 space-y-6">
            <h2 class="text-lg font-extrabold">{{ $lost ? '¿Dónde la vieron por última vez?' : '¿Dónde la encontraste?' }}</h2>
            <x-map-picker :lat="old('latitude', $report->latitude)" :lng="old('longitude', $report->longitude)" reference-input="location_reference"
                          :color="$lost ? '#f43f5e' : '#059669'" :emoji="$lost ? '❓' : '📍'" />
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="location_reference" class="block text-sm font-bold mb-1">Punto de referencia <span class="font-normal text-ink-500">(opcional)</span></label>
                    <input id="location_reference" type="text" name="location_reference" maxlength="150"
                           value="{{ old('location_reference', $report->location_reference) }}" data-touched="1" class="{{ $input }}">
                </div>
                <div>
                    <label for="event_date" class="block text-sm font-bold mb-1">{{ $lost ? '¿Cuándo se perdió?' : '¿Cuándo la encontraste?' }} <span class="text-rose-500">*</span></label>
                    <input id="event_date" type="date" name="event_date" value="{{ old('event_date', optional($report->event_date)->toDateString()) }}"
                           min="{{ $minDate->toDateString() }}" max="{{ now()->toDateString() }}" class="{{ $input }}">
                </div>
            </div>
        </section>

        {{-- Foto y contacto --}}
        <section class="p-5 sm:p-8 space-y-6">
            <h2 class="text-lg font-extrabold">Foto, señas y contacto</h2>
            <div class="grid sm:grid-cols-[200px_1fr] gap-5">
                <div>
                    <p class="block text-sm font-bold mb-1">Foto</p>
                    <label for="photo" class="relative block aspect-square rounded-2xl border-2 border-dashed border-ink-200 bg-ink-50 hover:bg-ink-100 cursor-pointer overflow-hidden">
                        @if ($report->photo_url)
                            <img id="photo-preview" src="{{ $report->photo_url }}" alt="Foto actual" class="absolute inset-0 w-full h-full object-cover">
                        @else
                            <img id="photo-preview" class="hidden absolute inset-0 w-full h-full object-cover" alt="">
                        @endif
                        <span class="absolute bottom-2 left-2 right-2 rounded-lg bg-ink-900/80 text-white text-xs font-bold text-center py-1.5">📷 Cambiar foto</span>
                    </label>
                    <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                    <p class="text-xs text-ink-500 mt-1">Opcional. JPG, PNG o WEBP · máx. 5 MB.</p>
                </div>
                <div>
                    <label for="description" class="block text-sm font-bold mb-1">Señas particulares <span class="text-rose-500">*</span></label>
                    <textarea id="description" name="description" rows="6" maxlength="500" class="{{ $input }}">{{ old('description', $report->description) }}</textarea>
                    <p class="text-xs text-ink-500 mt-1">Entre 15 y 500 caracteres.</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="contact_phone" class="block text-sm font-bold mb-1">Celular de contacto <span class="text-rose-500">*</span></label>
                    <input id="contact_phone" type="tel" name="contact_phone" inputmode="numeric" maxlength="14"
                           value="{{ old('contact_phone', $report->contact_phone) }}" class="{{ $input }}">
                    <label class="inline-flex items-center gap-2 mt-2 text-sm">
                        <input type="checkbox" name="contact_whatsapp" value="1" class="w-4 h-4 accent-emerald-600" @checked(old('contact_whatsapp', $report->contact_whatsapp))>
                        Este número tiene WhatsApp
                    </label>
                </div>
                <div>
                    <label for="contact_email" class="block text-sm font-bold mb-1">Correo <span class="font-normal text-ink-500">(opcional)</span></label>
                    <input id="contact_email" type="email" name="contact_email" maxlength="150"
                           value="{{ old('contact_email', $report->contact_email) }}" class="{{ $input }}">
                    <p class="text-xs text-ink-500 mt-1">Si lo dejas, te avisamos cuando aparezca un reporte parecido.</p>
                </div>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3 px-5 sm:px-8 py-4">
            <a href="{{ route('reports.manage', $report->management_token) }}" class="px-4 py-3 rounded-xl font-bold text-ink-700 hover:bg-ink-100">Cancelar</a>
            <button type="submit" class="px-6 py-3 rounded-xl font-extrabold text-white bg-ink-900 hover:bg-ink-700">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const MAX_COLORS = {{ (int) config('pets.max_colors') }};
    document.querySelectorAll('input[name="colors[]"]').forEach(c => c.addEventListener('change', () => {
        if (document.querySelectorAll('input[name="colors[]"]:checked').length > MAX_COLORS) c.checked = false;
    }));
    const photo = document.getElementById('photo');
    photo.addEventListener('change', () => {
        const f = photo.files[0], img = document.getElementById('photo-preview');
        if (!f) return;
        img.src = URL.createObjectURL(f);
        img.classList.remove('hidden');
    });
})();
</script>
@endpush
