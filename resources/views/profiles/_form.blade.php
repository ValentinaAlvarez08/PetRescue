{{-- Sprint 4 — HU-10: campos del perfil digital (crear y editar con el enlace privado) --}}
@php
    $input = 'w-full rounded-xl border border-ink-200 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-400';
    $err = fn ($field) => $errors->has($field) ? ' border-rose-400 bg-rose-50' : '';
    $value = fn (string $key, $default = null) => old($key, $profile?->{$key} ?? $default);
    $selectedColors = old('colors', $profile?->color_keys ?? []);
@endphp

@if ($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">
        <p class="font-bold mb-1">Revisa estos datos:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="space-y-5">
    <h2 class="text-lg font-extrabold">🐾 Sobre tu mascota</h2>

    <div>
        <p class="block text-sm font-bold mb-2">¿Qué animal es? <span class="text-rose-500">*</span></p>
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
            @foreach ($species as $key => $sp)
                <label class="cursor-pointer">
                    <input type="radio" name="species" value="{{ $key }}" class="peer sr-only" @checked($value('species') === $key)>
                    <span class="flex flex-col items-center justify-center gap-1 h-20 rounded-2xl border-2 border-ink-100 bg-ink-50 peer-checked:border-brand-500 peer-checked:bg-brand-50 text-center px-1">
                        <span class="text-2xl">{{ $sp['emoji'] }}</span>
                        <span class="text-xs font-bold leading-tight">{{ $sp['label'] }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label for="name" class="block text-sm font-bold mb-1">Nombre <span class="text-rose-500">*</span></label>
            <input id="name" name="name" type="text" maxlength="40" value="{{ $value('name') }}" placeholder="Ej: Luna" class="{{ $input }}{{ $err('name') }}">
        </div>
        <div>
            <label for="breed" class="block text-sm font-bold mb-1">Raza <span class="text-rose-500">*</span></label>
            <select id="breed" name="breed" data-old="{{ $value('breed') }}" class="{{ $input }}{{ $err('breed') }}">
                <option value="">Primero elige el animal</option>
            </select>
        </div>
        <div>
            <p class="block text-sm font-bold mb-2">Sexo <span class="text-rose-500">*</span></p>
            <div class="inline-flex rounded-xl bg-ink-100 p-1 gap-1">
                @foreach ($sexes as $key => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="sex" value="{{ $key }}" class="peer sr-only" @checked($value('sex') === $key)>
                        <span class="block px-4 py-2 rounded-lg text-sm font-bold text-ink-500 peer-checked:bg-white peer-checked:text-ink-900 peer-checked:shadow">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div>
            <label for="birth_date" class="block text-sm font-bold mb-1">Fecha de nacimiento <span class="font-normal text-ink-500">(aproximada)</span></label>
            <input id="birth_date" name="birth_date" type="date" max="{{ now()->toDateString() }}"
                   value="{{ old('birth_date', $profile?->birth_date?->toDateString()) }}" class="{{ $input }}{{ $err('birth_date') }}">
        </div>
    </div>

    <div id="size-field" class="hidden">
        <p class="block text-sm font-bold mb-2">Tamaño <span class="text-rose-500">*</span></p>
        <div class="grid sm:grid-cols-3 gap-2">
            @foreach ($sizes as $key => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="size" value="{{ $key }}" class="peer sr-only" @checked($value('size') === $key)>
                    <span class="block rounded-xl border-2 border-ink-100 px-4 py-3 text-sm font-bold peer-checked:border-brand-500 peer-checked:bg-brand-50">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <p class="block text-sm font-bold mb-1">Colores principales <span class="text-rose-500">*</span></p>
        <p class="text-xs text-ink-500 mb-2">Elige hasta {{ config('pets.max_colors') }}.</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($colors as $key => $color)
                <label class="cursor-pointer">
                    <input type="checkbox" name="colors[]" value="{{ $key }}" class="peer sr-only" @checked(in_array($key, $selectedColors, true))>
                    <span class="inline-flex items-center gap-2 rounded-full border-2 border-ink-100 bg-white pl-1.5 pr-3 py-1 text-sm font-semibold peer-checked:border-ink-900 peer-checked:bg-ink-900 peer-checked:text-white">
                        <span class="w-5 h-5 rounded-full border border-ink-200" style="background: {{ $color['hex'] }}"></span>
                        {{ $color['label'] }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="grid sm:grid-cols-[180px_1fr] gap-5">
        <div>
            <p class="block text-sm font-bold mb-1">Foto {!! $profile ? '' : '<span class="text-rose-500">*</span>' !!}</p>
            <label for="photo" class="relative flex flex-col items-center justify-center gap-1 aspect-square rounded-2xl border-2 border-dashed border-ink-200 bg-ink-50 hover:bg-ink-100 cursor-pointer overflow-hidden text-center px-3">
                <img id="photo-preview" src="{{ $profile?->photo_url }}" class="{{ $profile ? '' : 'hidden' }} absolute inset-0 w-full h-full object-cover" alt="">
                <span class="text-3xl">📷</span>
                <span class="text-sm font-bold">{{ $profile ? 'Cambiar foto' : 'Subir foto' }}</span>
            </label>
            <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only">
            <p class="text-xs text-ink-500 mt-1">JPG, PNG o WEBP · máx. 5 MB.</p>
        </div>
        <div>
            <label for="distinctive_marks" class="block text-sm font-bold mb-1">Señas particulares</label>
            <textarea id="distinctive_marks" name="distinctive_marks" rows="5" maxlength="500"
                      placeholder="Ej: mancha blanca en el pecho, oreja izquierda caída, collar rojo con placa."
                      class="{{ $input }}{{ $err('distinctive_marks') }}">{{ $value('distinctive_marks') }}</textarea>
        </div>
    </div>
</section>

<section class="space-y-5">
    <h2 class="text-lg font-extrabold">❤️ Salud</h2>
    <div>
        <p class="block text-sm font-bold mb-2">¿Está esterilizada? <span class="text-rose-500">*</span></p>
        <div class="inline-flex rounded-xl bg-ink-100 p-1 gap-1">
            @foreach ($sterilizedOptions as $key => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="sterilized" value="{{ $key }}" class="peer sr-only" @checked($value('sterilized', 'no_se') === $key)>
                    <span class="block px-4 py-2 rounded-lg text-sm font-bold text-ink-500 peer-checked:bg-white peer-checked:text-ink-900 peer-checked:shadow">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label for="vaccines" class="block text-sm font-bold mb-1">Vacunas <span class="font-normal text-ink-500">(opcional)</span></label>
            <textarea id="vaccines" name="vaccines" rows="3" maxlength="300" placeholder="Ej: antirrábica marzo 2026, polivalente al día."
                      class="{{ $input }}">{{ $value('vaccines') }}</textarea>
        </div>
        <div>
            <label for="health_notes" class="block text-sm font-bold mb-1">Alergias, medicamentos o cuidados <span class="font-normal text-ink-500">(opcional)</span></label>
            <textarea id="health_notes" name="health_notes" rows="3" maxlength="500" placeholder="Ej: es alérgica al pollo; toma medicamento para el corazón cada 12 horas."
                      class="{{ $input }}">{{ $value('health_notes') }}</textarea>
        </div>
    </div>
</section>

<section class="space-y-5">
    <h2 class="text-lg font-extrabold">📞 Contacto de la familia</h2>
    <p class="text-sm text-ink-500 -mt-3">Lo verá quien abra el perfil o escanee el QR de la placa. No pongas tu dirección exacta.</p>
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label for="owner_name" class="block text-sm font-bold mb-1">Tu nombre <span class="text-rose-500">*</span></label>
            <input id="owner_name" name="owner_name" type="text" maxlength="80" value="{{ $value('owner_name') }}" class="{{ $input }}{{ $err('owner_name') }}">
        </div>
        <div>
            <label for="neighborhood" class="block text-sm font-bold mb-1">Barrio <span class="font-normal text-ink-500">(opcional)</span></label>
            <input id="neighborhood" name="neighborhood" type="text" maxlength="150" value="{{ $value('neighborhood') }}" placeholder="Ej: San Ignacio, Pasto" class="{{ $input }}">
        </div>
        <div>
            <label for="contact_phone" class="block text-sm font-bold mb-1">Celular <span class="text-rose-500">*</span></label>
            <input id="contact_phone" name="contact_phone" type="tel" inputmode="numeric" maxlength="14" value="{{ $value('contact_phone') }}" placeholder="300 123 4567"
                   class="{{ $input }}{{ $err('contact_phone') }}">
            <label class="inline-flex items-center gap-2 mt-2 text-sm">
                <input type="checkbox" name="contact_whatsapp" value="1" class="w-4 h-4 accent-emerald-600" @checked($value('contact_whatsapp', true))>
                Este número tiene WhatsApp
            </label>
        </div>
        <div>
            <label for="contact_email" class="block text-sm font-bold mb-1">Correo <span class="font-normal text-ink-500">(opcional)</span></label>
            <input id="contact_email" name="contact_email" type="email" maxlength="150" value="{{ $value('contact_email') }}" class="{{ $input }}{{ $err('contact_email') }}">
        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    const SPECIES = @json($species);
    const MAX_COLORS = {{ (int) config('pets.max_colors') }};
    const breed = document.getElementById('breed');

    function onSpeciesChange() {
        const checked = document.querySelector('input[name="species"]:checked');
        if (!checked) return;
        const sp = SPECIES[checked.value];
        const previous = breed.value || breed.dataset.old;
        breed.innerHTML = '<option value="">Selecciona...</option>';
        sp.breeds.forEach(b => {
            const o = document.createElement('option');
            o.value = b; o.textContent = b;
            if (b === previous) o.selected = true;
            breed.appendChild(o);
        });
        breed.dataset.old = '';
        document.getElementById('size-field').classList.toggle('hidden', !sp.uses_size);
    }
    document.querySelectorAll('input[name="species"]').forEach(r => r.addEventListener('change', onSpeciesChange));
    onSpeciesChange();

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
