@extends('layouts.app')

@php
    $lost = $type === 'perdida';
    $accent = $lost ? 'rose' : 'emerald';
    $pinColor = $lost ? '#f43f5e' : '#059669';

    // Paso donde está el primer error del servidor, para abrir el formulario ahí.
    $stepFields = [
        1 => ['species', 'breed', 'pet_name', 'sex', 'size', 'colors', 'colors.*'],
        2 => ['latitude', 'longitude', 'location_reference', 'event_date'],
        3 => ['photo', 'description', 'contact_phone', 'contact_email', 'contact_whatsapp'],
    ];
    $startStep = 1;
    foreach ($stepFields as $n => $fields) {
        if (collect($fields)->contains(fn ($f) => $errors->has($f))) { $startStep = $n; break; }
    }
    $oldColors = old('colors', []);
    $minDate = now()->subDays(config('pets.max_days_ago'))->toDateString();
@endphp

@section('title', $lost ? 'Reportar mascota perdida — PetRescue' : 'Reportar mascota encontrada — PetRescue')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full
                     {{ $lost ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
            {{ $lost ? 'Mascota perdida' : 'Mascota encontrada' }}
        </span>
        <h1 class="text-3xl sm:text-4xl font-black mt-3 tracking-tight">
            {{ $lost ? 'Vamos a buscarla juntos' : 'Gracias por ayudar' }}
        </h1>
        <p class="text-ink-500 mt-2">
            Son 3 pasos cortos, sin crear cuenta. Al final recibirás un enlace privado para actualizar o cerrar el reporte.
        </p>
    </div>

    {{-- Barra de progreso --}}
    <ol id="stepper" class="grid grid-cols-3 gap-2 mb-6 text-xs sm:text-sm font-bold">
        @foreach ([1 => 'La mascota', 2 => 'Dónde y cuándo', 3 => 'Foto y contacto'] as $n => $label)
            <li data-step-dot="{{ $n }}" class="rounded-xl px-3 py-2 border border-ink-200 bg-white text-ink-500 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full grid place-items-center bg-ink-100 text-ink-700 shrink-0" data-dot-num>{{ $n }}</span>
                <span class="truncate">{{ $label }}</span>
            </li>
        @endforeach
    </ol>

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

    <form id="report-form" action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" novalidate
          class="bg-white rounded-3xl shadow-soft border border-ink-100">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        {{-- ============ PASO 1: la mascota ============ --}}
        <section data-step="1" class="p-5 sm:p-8 space-y-6">
            <div>
                <h2 class="text-lg font-extrabold">¿Qué animal {{ $lost ? 'perdiste' : 'encontraste' }}?</h2>
                <p class="text-sm text-ink-500">Solo aceptamos mascotas domésticas.</p>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-3">
                    @foreach ($species as $key => $sp)
                        <label class="cursor-pointer">
                            <input type="radio" name="species" value="{{ $key }}" class="peer sr-only" @checked(old('species') === $key) required>
                            <span class="flex flex-col items-center justify-center gap-1 h-24 rounded-2xl border-2 border-ink-100 bg-ink-50
                                         peer-checked:border-{{ $accent }}-500 peer-checked:bg-{{ $accent }}-50 hover:border-ink-200 transition text-center px-1">
                                <span class="text-3xl">{{ $sp['emoji'] }}</span>
                                <span class="text-xs sm:text-sm font-bold leading-tight">{{ $sp['label'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="field-error hidden text-sm text-rose-600 mt-2" data-for="species">Elige qué animal es.</p>
            </div>

            <div id="pet-details" class="space-y-6 {{ old('species') ? '' : 'hidden' }}">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="breed" class="block text-sm font-bold mb-1">
                            Raza {!! $lost ? '<span class="text-rose-500">*</span>' : '<span class="font-normal text-ink-500">(opcional)</span>' !!}
                        </label>
                        <select id="breed" name="breed" data-old="{{ old('breed') }}" @if($lost) required @endif
                                class="w-full rounded-xl border border-ink-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </select>
                        <p class="field-error hidden text-sm text-rose-600 mt-1" data-for="breed">Elige la raza.</p>
                    </div>
                    <div>
                        <label for="pet_name" class="block text-sm font-bold mb-1">
                            {{ $lost ? '¿Cómo se llama?' : 'Nombre en la placa' }}
                            <span class="font-normal text-ink-500">(opcional)</span>
                        </label>
                        <input id="pet_name" type="text" name="pet_name" value="{{ old('pet_name') }}" maxlength="40"
                               placeholder="{{ $lost ? 'Ej: Luna' : 'Si tiene placa o collar con nombre' }}"
                               class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                </div>

                <div>
                    <p class="block text-sm font-bold mb-2">Sexo <span class="text-rose-500">*</span></p>
                    <div class="inline-flex rounded-xl bg-ink-100 p-1 gap-1">
                        @foreach ($sexes as $key => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="sex" value="{{ $key }}" class="peer sr-only" @checked(old('sex', $lost ? null : 'no_se') === $key)>
                                <span class="block px-4 py-2 rounded-lg text-sm font-bold text-ink-500 peer-checked:bg-white peer-checked:text-ink-900 peer-checked:shadow">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="field-error hidden text-sm text-rose-600 mt-1" data-for="sex">Indica el sexo (o "No sé").</p>
                </div>

                <div id="size-field" class="hidden">
                    <p class="block text-sm font-bold mb-2">Tamaño <span class="text-rose-500">*</span></p>
                    <div class="grid sm:grid-cols-3 gap-2">
                        @foreach ($sizes as $key => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="size" value="{{ $key }}" class="peer sr-only" @checked(old('size') === $key)>
                                <span class="block rounded-xl border-2 border-ink-100 px-4 py-3 text-sm font-bold peer-checked:border-{{ $accent }}-500 peer-checked:bg-{{ $accent }}-50">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="field-error hidden text-sm text-rose-600 mt-1" data-for="size">Elige el tamaño.</p>
                </div>

                <div>
                    <p class="block text-sm font-bold mb-1">Colores principales <span class="text-rose-500">*</span></p>
                    <p class="text-xs text-ink-500 mb-2">Elige hasta {{ config('pets.max_colors') }}.</p>
                    <div id="color-chips" class="flex flex-wrap gap-2">
                        @foreach ($colors as $key => $color)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="colors[]" value="{{ $key }}" class="peer sr-only" @checked(in_array($key, $oldColors, true))>
                                <span class="inline-flex items-center gap-2 rounded-full border-2 border-ink-100 bg-white pl-1.5 pr-3 py-1 text-sm font-semibold
                                             peer-checked:border-ink-900 peer-checked:bg-ink-900 peer-checked:text-white">
                                    <span class="w-5 h-5 rounded-full border border-ink-200" style="background: {{ $color['hex'] }}"></span>
                                    {{ $color['label'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <p class="field-error hidden text-sm text-rose-600 mt-1" data-for="colors">Elige al menos un color.</p>
                </div>
            </div>
        </section>

        {{-- ============ PASO 2: dónde y cuándo ============ --}}
        <section data-step="2" class="hidden p-5 sm:p-8 space-y-6">
            <div>
                <h2 class="text-lg font-extrabold">{{ $lost ? '¿Dónde la viste por última vez?' : '¿Dónde la encontraste?' }}</h2>
                <p class="text-sm text-ink-500 mb-3">Toca el mapa para poner el pin, busca la dirección o usa tu ubicación.</p>
                <x-map-picker :lat="old('latitude')" :lng="old('longitude')" reference-input="location_reference"
                              :color="$pinColor" :emoji="$lost ? '❓' : '📍'" />
                <p class="field-error hidden text-sm text-rose-600 mt-1" data-for="latitude">Marca el lugar en el mapa.</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="location_reference" class="block text-sm font-bold mb-1">
                        Punto de referencia <span class="font-normal text-ink-500">(opcional)</span>
                    </label>
                    <input id="location_reference" type="text" name="location_reference" value="{{ old('location_reference') }}" maxlength="150"
                           placeholder="Ej: frente a la panadería, barrio San Ignacio"
                           class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label for="event_date" class="block text-sm font-bold mb-1">
                        {{ $lost ? '¿Cuándo se perdió?' : '¿Cuándo la encontraste?' }} <span class="text-rose-500">*</span>
                    </label>
                    <input id="event_date" type="date" name="event_date" value="{{ old('event_date', now()->toDateString()) }}"
                           min="{{ $minDate }}" max="{{ now()->toDateString() }}" required
                           class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <p class="field-error hidden text-sm text-rose-600 mt-1" data-for="event_date">Elige una fecha de los últimos {{ config('pets.max_days_ago') }} días.</p>
                </div>
            </div>
        </section>

        {{-- ============ PASO 3: foto y contacto ============ --}}
        <section data-step="3" class="hidden p-5 sm:p-8 space-y-6">
            <div class="grid sm:grid-cols-[200px_1fr] gap-5">
                <div>
                    <p class="block text-sm font-bold mb-1">Foto <span class="text-rose-500">*</span></p>
                    <label for="photo" id="photo-drop"
                           class="relative flex flex-col items-center justify-center gap-1 aspect-square rounded-2xl border-2 border-dashed border-ink-200 bg-ink-50 hover:bg-ink-100 cursor-pointer overflow-hidden text-center px-3">
                        <img id="photo-preview" class="hidden absolute inset-0 w-full h-full object-cover" alt="">
                        <span class="text-3xl">📷</span>
                        <span class="text-sm font-bold">Subir foto</span>
                        <span class="text-xs text-ink-500">JPG, PNG o WEBP · máx. 5 MB</span>
                    </label>
                    <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only" required>
                    <p class="field-error hidden text-sm text-rose-600 mt-1" data-for="photo">Sube una foto (máx. 5 MB).</p>
                </div>
                <div>
                    <label for="description" class="block text-sm font-bold mb-1">Señas particulares <span class="text-rose-500">*</span></label>
                    <textarea id="description" name="description" rows="6" maxlength="500" required
                              placeholder="{{ $lost ? 'Ej: collar rojo con placa, mancha blanca en el pecho, cojea un poco de la pata trasera. Es miedosa, no la persigan.' : 'Ej: tiene collar azul sin placa, está bien de salud, es muy dócil. La tengo en mi casa.' }}"
                              class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400">{{ old('description') }}</textarea>
                    <div class="flex justify-between text-xs text-ink-500 mt-1">
                        <span class="field-error hidden text-rose-600" data-for="description">Escribe al menos 15 caracteres.</span>
                        <span class="ml-auto"><span id="desc-count">0</span>/500</span>
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="contact_phone" class="block text-sm font-bold mb-1">Celular de contacto <span class="text-rose-500">*</span></label>
                    <input id="contact_phone" type="tel" name="contact_phone" value="{{ old('contact_phone') }}" inputmode="numeric"
                           placeholder="300 123 4567" maxlength="14" required
                           class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <label class="inline-flex items-center gap-2 mt-2 text-sm">
                        <input type="checkbox" name="contact_whatsapp" value="1" class="w-4 h-4 accent-emerald-600" @checked(old('contact_whatsapp', true))>
                        Este número tiene WhatsApp
                    </label>
                    <p class="field-error hidden text-sm text-rose-600 mt-1" data-for="contact_phone">Celular de 10 dígitos que empiece por 3.</p>
                </div>
                <div>
                    <label for="contact_email" class="block text-sm font-bold mb-1">Correo <span class="font-normal text-ink-500">(opcional)</span></label>
                    <input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email') }}" maxlength="150"
                           placeholder="tucorreo@ejemplo.com"
                           class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            <div id="summary" class="rounded-2xl bg-ink-50 border border-ink-100 p-4 text-sm"></div>
        </section>

        {{-- Navegación entre pasos --}}
        <div class="flex items-center justify-between gap-3 border-t border-ink-100 px-5 sm:px-8 py-4">
            <button type="button" id="btn-prev" class="invisible px-4 py-3 rounded-xl font-bold text-ink-700 hover:bg-ink-100">← Atrás</button>
            <button type="button" id="btn-next"
                    class="px-6 py-3 rounded-xl font-extrabold text-white bg-ink-900 hover:bg-ink-700">Continuar →</button>
            <button type="submit" id="btn-submit"
                    class="hidden px-6 py-3 rounded-xl font-extrabold text-white {{ $lost ? 'bg-rose-500 hover:bg-rose-600' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                Publicar reporte
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const SPECIES = @json($species);
    const UNKNOWN_BREED = @json(config('pets.unknown_breed'));
    const MAX_COLORS = {{ (int) config('pets.max_colors') }};
    const LOST = @json($lost);
    const COLORS = @json(collect($colors)->map(fn ($c) => $c['label']));
    const SEXES = @json($sexes);
    const form = document.getElementById('report-form');
    let step = {{ $startStep }};

    // ---------- Paso 1: especie -> razas / tamaño ----------
    const breed = document.getElementById('breed');
    function speciesValue() {
        const r = form.querySelector('input[name="species"]:checked');
        return r ? r.value : null;
    }
    function onSpeciesChange() {
        const sp = speciesValue();
        if (!sp) return;
        document.getElementById('pet-details').classList.remove('hidden');
        const previous = breed.value || breed.dataset.old;
        breed.innerHTML = '<option value="">Selecciona...</option>';
        const options = SPECIES[sp].breeds.slice();
        if (!LOST) options.unshift(UNKNOWN_BREED);
        options.forEach(b => {
            const o = document.createElement('option');
            o.value = b; o.textContent = b;
            if (b === previous) o.selected = true;
            breed.appendChild(o);
        });
        breed.dataset.old = '';
        document.getElementById('size-field').classList.toggle('hidden', !SPECIES[sp].uses_size);
    }
    form.querySelectorAll('input[name="species"]').forEach(r => r.addEventListener('change', onSpeciesChange));
    onSpeciesChange();

    // Colores: máximo N
    const chips = form.querySelectorAll('input[name="colors[]"]');
    chips.forEach(c => c.addEventListener('change', () => {
        const checked = form.querySelectorAll('input[name="colors[]"]:checked');
        if (checked.length > MAX_COLORS) c.checked = false;
    }));

    // ---------- Paso 3: foto, contador, teléfono ----------
    const photo = document.getElementById('photo');
    photo.addEventListener('change', () => {
        const f = photo.files[0];
        const img = document.getElementById('photo-preview');
        if (!f) { img.classList.add('hidden'); return; }
        img.src = URL.createObjectURL(f);
        img.classList.remove('hidden');
    });
    const desc = document.getElementById('description');
    const count = () => document.getElementById('desc-count').textContent = desc.value.length;
    desc.addEventListener('input', count); count();

    const phone = document.getElementById('contact_phone');
    phone.addEventListener('input', () => {
        const d = phone.value.replace(/\D/g, '').slice(0, 10);
        phone.value = d.replace(/^(\d{3})(\d{0,3})(\d{0,4}).*/, (m, a, b, c) => [a, b, c].filter(Boolean).join(' '));
    });

    // ---------- Validación por paso (el servidor valida de nuevo) ----------
    function showError(field, show) {
        const el = form.querySelector('.field-error[data-for="' + field + '"]');
        if (el) el.classList.toggle('hidden', !show);
        return !show;
    }
    const validators = {
        1: () => {
            const sp = speciesValue();
            let ok = showError('species', !sp);
            if (!sp) return false;
            ok = showError('breed', LOST && !breed.value) && ok;
            ok = showError('sex', !form.querySelector('input[name="sex"]:checked')) && ok;
            if (SPECIES[sp].uses_size) ok = showError('size', !form.querySelector('input[name="size"]:checked')) && ok;
            ok = showError('colors', form.querySelectorAll('input[name="colors[]"]:checked').length === 0) && ok;
            return ok;
        },
        2: () => {
            let ok = showError('latitude', !form.querySelector('input[name="latitude"]').value);
            const d = document.getElementById('event_date');
            ok = showError('event_date', !d.value || d.value < d.min || d.value > d.max) && ok;
            return ok;
        },
        3: () => {
            const f = photo.files[0];
            let ok = showError('photo', !f || f.size > 5 * 1024 * 1024);
            ok = showError('description', desc.value.trim().length < 15) && ok;
            ok = showError('contact_phone', !/^3\d{9}$/.test(phone.value.replace(/\D/g, ''))) && ok;
            return ok;
        },
    };

    function renderSummary() {
        const sp = speciesValue();
        const cols = [...form.querySelectorAll('input[name="colors[]"]:checked')].map(c => COLORS[c.value]).join(', ');
        const sex = form.querySelector('input[name="sex"]:checked');
        const ref = document.getElementById('location_reference').value;
        document.getElementById('summary').innerHTML =
            '<p class="font-extrabold mb-1">Resumen</p>' +
            '<p>' + (sp ? SPECIES[sp].emoji + ' ' + SPECIES[sp].label : '') + (breed.value ? ' · ' + breed.value : '') +
            (sex ? ' · ' + SEXES[sex.value] : '') + (cols ? ' · ' + cols : '') + '</p>' +
            '<p class="text-ink-500">📅 ' + document.getElementById('event_date').value + (ref ? ' · 📍 ' + ref.replace(/</g, '&lt;') : ' · 📍 punto marcado en el mapa') + '</p>';
    }

    // ---------- Navegación ----------
    function go(n) {
        step = n;
        form.querySelectorAll('[data-step]').forEach(s => s.classList.toggle('hidden', +s.dataset.step !== n));
        document.querySelectorAll('[data-step-dot]').forEach(d => {
            const k = +d.dataset.stepDot, num = d.querySelector('[data-dot-num]');
            d.className = 'rounded-xl px-3 py-2 border flex items-center gap-2 ' +
                (k === n ? 'border-ink-900 bg-white text-ink-900' : k < n ? 'border-ink-100 bg-ink-100 text-ink-700' : 'border-ink-200 bg-white text-ink-500');
            num.className = 'w-6 h-6 rounded-full grid place-items-center shrink-0 ' + (k < n ? 'bg-emerald-500 text-white' : k === n ? 'bg-ink-900 text-white' : 'bg-ink-100 text-ink-700');
            num.textContent = k < n ? '✓' : k;
        });
        document.getElementById('btn-prev').classList.toggle('invisible', n === 1);
        document.getElementById('btn-next').classList.toggle('hidden', n === 3);
        document.getElementById('btn-submit').classList.toggle('hidden', n !== 3);
        if (n === 2) form.querySelector('[data-step="2"] [id^="map-picker-"]').dispatchEvent(new Event('map:refresh'));
        if (n === 3) renderSummary();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    document.getElementById('btn-next').addEventListener('click', () => { if (validators[step]()) go(step + 1); });
    document.getElementById('btn-prev').addEventListener('click', () => go(step - 1));
    document.addEventListener('location:set', () => showError('latitude', false));
    // Al corregir un campo, se oculta su mensaje de error.
    form.addEventListener('change', e => {
        const name = (e.target.name || '').replace('[]', '');
        if (name) showError(name, false);
    });
    form.addEventListener('submit', e => {
        for (let n = 1; n <= 3; n++) {
            if (!validators[n]()) { e.preventDefault(); go(n); return; }
        }
        document.getElementById('btn-submit').disabled = true;
        document.getElementById('btn-submit').textContent = 'Publicando...';
    });
    go(step);
})();
</script>
@endpush
