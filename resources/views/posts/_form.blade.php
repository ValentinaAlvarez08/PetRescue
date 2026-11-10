{{-- Sprint 3 — HU-17: campos compartidos entre "Nueva publicación" y la edición privada --}}
@php
    $input = 'w-full rounded-xl border border-ink-200 bg-ink-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-400';
    $err = fn ($field) => $errors->has($field) ? ' border-rose-400 bg-rose-50' : '';
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

<div>
    <label for="nickname" class="block font-bold mb-1">Tu apodo</label>
    <input id="nickname" name="nickname" type="text" maxlength="40" required
           value="{{ old('nickname', $post->nickname ?? '') }}" placeholder="Ej. Mamá de Luna"
           class="{{ $input }}{{ $err('nickname') }}">
    <p class="text-xs text-ink-500 mt-1">Así te verá la comunidad. No uses tu nombre completo si no quieres.</p>
    @error('nickname') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label for="title" class="block font-bold mb-1">Título</label>
    <input id="title" name="title" type="text" maxlength="120" required
           value="{{ old('title', $post->title ?? '') }}" placeholder="Ej. ¿Cómo ayudo a mi perro rescatado a perder el miedo?"
           class="{{ $input }}{{ $err('title') }}">
    <p class="text-xs text-ink-500 mt-1">Máximo 120 caracteres.</p>
    @error('title') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label for="body" class="block font-bold mb-1">Cuéntale a la comunidad</label>
    <textarea id="body" name="body" rows="7" minlength="20" maxlength="2000" required
              placeholder="Describe tu pregunta o experiencia: edad de la mascota, qué has intentado, qué te ha funcionado..."
              class="{{ $input }}{{ $err('body') }}">{{ old('body', $post->body ?? '') }}</textarea>
    <p class="text-xs text-ink-500 mt-1">Entre 20 y 2000 caracteres. <span id="body-count"></span></p>
    @error('body') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label for="photo" class="block font-bold mb-1">Foto <span class="font-normal text-ink-500">(opcional)</span></label>
    @if (! empty($post?->photo_url))
        <div class="flex items-center gap-4 mb-3">
            <img src="{{ $post->photo_url }}" alt="" class="w-24 h-24 rounded-2xl object-cover">
            <label class="flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" name="remove_photo" value="1" class="rounded"> Quitar la foto
            </label>
        </div>
    @endif
    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
           class="block w-full text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:font-bold file:text-brand-700 hover:file:bg-brand-100">
    <p class="text-xs text-ink-500 mt-1">JPG, PNG o WEBP de máximo 5 MB.</p>
    @error('photo') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
</div>

@push('scripts')
<script>
(function () {
    const body = document.getElementById('body');
    const count = document.getElementById('body-count');
    const update = () => { count.textContent = '(' + body.value.trim().length + '/2000)'; };
    body.addEventListener('input', update);
    update();
})();
</script>
@endpush
