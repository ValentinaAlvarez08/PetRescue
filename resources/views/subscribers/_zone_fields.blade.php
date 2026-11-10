{{-- Sprint 4 — HU-11 (CID 1): nombre de la zona, punto en el mapa y radio de 1 a 20 km. --}}
<div>
    <label for="label" class="block text-sm font-bold mb-1">Nombre de la zona <span class="text-rose-500">*</span></label>
    <input id="label" type="text" name="label" value="{{ old('label') }}" required maxlength="40" list="zone-labels"
           placeholder="Ej: Casa, Trabajo, Finca" class="w-full rounded-xl border border-ink-200 px-4 py-3">
    <datalist id="zone-labels">
        @foreach ($alerts['suggested_labels'] as $suggestion)
            <option value="{{ $suggestion }}">
        @endforeach
    </datalist>
    <div class="mt-2 flex flex-wrap gap-2">
        @foreach (array_slice($alerts['suggested_labels'], 0, 3) as $suggestion)
            <button type="button" data-zone-label="{{ $suggestion }}"
                    class="rounded-full border border-ink-200 bg-white hover:bg-brand-50 hover:border-brand-200 px-3 py-1 text-xs font-bold">{{ $suggestion }}</button>
        @endforeach
    </div>
</div>

<div>
    <p class="block text-sm font-bold mb-1">Zona a vigilar <span class="text-rose-500">*</span></p>
    <x-map-picker :lat="old('latitude')" :lng="old('longitude')" color="#f97316" emoji="🏠" height="h-72" radius-input="radius_km" />
</div>

<div>
    <label for="radius_km" class="block text-sm font-bold mb-1">
        Radio: <span id="radius-label">{{ old('radius_km', 5) }}</span> km
    </label>
    <input id="radius_km" type="range" min="{{ $alerts['radius_min'] }}" max="{{ $alerts['radius_max'] }}" step="1" name="radius_km"
           value="{{ old('radius_km', 5) }}" class="w-full accent-brand-500"
           oninput="document.getElementById('radius-label').textContent = this.value">
    <div class="flex justify-between text-xs text-ink-500 font-semibold">
        <span>{{ $alerts['radius_min'] }} km</span><span>{{ $alerts['radius_max'] }} km</span>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-zone-label]').forEach(b => b.addEventListener('click', () => {
    document.getElementById('label').value = b.dataset.zoneLabel;
}));
</script>
@endpush
