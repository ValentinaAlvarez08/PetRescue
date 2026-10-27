@extends('layouts.app')

@section('title', 'Directorio de servicios para mascotas — PetRescue')

@section('content')
<div class="mb-6">
    <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Directorio</p>
    <h1 class="text-3xl font-black tracking-tight">Servicios para mascotas en el mapa</h1>
    <p class="text-ink-500 mt-1">Toca un lugar en el mapa o en la lista para ver a qué se dedica y cómo contactarlo.</p>
</div>

{{-- Filtros por categoría --}}
<div id="filters" class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4">
    <button type="button" data-cat=""
            class="filter-btn shrink-0 px-4 py-2 rounded-full text-sm font-bold border">Todos</button>
    @foreach ($categories as $key => $cat)
        <button type="button" data-cat="{{ $key }}"
                class="filter-btn shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold border">
            <span>{{ $cat['emoji'] }}</span>{{ $cat['short'] }}
        </button>
    @endforeach
</div>

<div class="grid lg:grid-cols-[1fr_380px] gap-4">
    <div class="relative">
        <div id="dir-map" class="w-full h-[420px] lg:h-[620px] rounded-3xl border border-ink-100 shadow-soft z-0"></div>

        {{-- Ficha del lugar seleccionado --}}
        <div id="detail" class="hidden absolute z-[600] left-3 right-3 bottom-3 sm:left-auto sm:w-96 bg-white rounded-3xl shadow-2xl border border-ink-100 p-5"></div>
    </div>

    <div class="bg-white rounded-3xl border border-ink-100 shadow-soft overflow-hidden flex flex-col lg:h-[620px]">
        <div class="px-5 py-4 border-b border-ink-100 flex items-center justify-between">
            <p class="font-extrabold"><span id="count">0</span> lugares</p>
            <input id="q" type="search" placeholder="Buscar por nombre..." class="w-44 rounded-lg border border-ink-200 px-3 py-1.5 text-sm">
        </div>
        <ul id="list" class="divide-y divide-ink-100 overflow-y-auto max-h-96 lg:max-h-none flex-1"></ul>
    </div>
</div>

<p class="text-xs text-ink-500 mt-4">¿Tienes un negocio para mascotas? Muy pronto podrás pedir que lo agreguemos al directorio.</p>
@endsection

@push('scripts')
<script>
(function () {
    const PLACES = @json($mapBusinesses);
    const CATS = @json($categories);
    const c = @json($center);
    let current = @json($selected ?? '');
    let search = '';

    const esc = s => String(s ?? '').replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
    const map = L.map('dir-map').setView([c.lat, c.lng], c.zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);

    const markers = {};
    PLACES.forEach(p => {
        markers[p.id] = L.marker([p.lat, p.lng], { icon: L.divIcon({ className: '', iconSize: [36, 36], iconAnchor: [18, 36],
            html: '<div class="pin" style="background:' + p.color + '"><span>' + p.emoji + '</span></div>' }) })
            .on('click', () => select(p.id));
    });

    function visible() {
        return PLACES.filter(p => (!current || p.category === current) &&
            (!search || (p.name + ' ' + p.description + ' ' + p.services.join(' ')).toLowerCase().includes(search)));
    }

    function render() {
        const list = document.getElementById('list');
        const items = visible();
        document.getElementById('count').textContent = items.length;
        Object.values(markers).forEach(m => m.remove());
        list.innerHTML = '';
        items.forEach(p => {
            markers[p.id].addTo(map);
            const li = document.createElement('li');
            li.className = 'px-5 py-4 hover:bg-ink-50 cursor-pointer flex gap-3';
            li.dataset.id = p.id;
            li.innerHTML =
                '<span class="w-10 h-10 shrink-0 rounded-xl grid place-items-center text-lg" style="background:' + p.color + '1f">' + p.emoji + '</span>' +
                '<span class="min-w-0"><span class="block font-extrabold truncate">' + esc(p.name) + '</span>' +
                '<span class="block text-xs font-bold" style="color:' + p.color + '">' + esc(p.category_label) + '</span>' +
                '<span class="block text-sm text-ink-500 truncate">' + esc(p.address) + '</span></span>';
            li.addEventListener('click', () => select(p.id, true));
            list.appendChild(li);
        });
        if (!items.length) list.innerHTML = '<li class="px-5 py-10 text-center text-ink-500">No hay lugares con ese filtro.</li>';
        const group = items.map(p => markers[p.id]);
        if (group.length) map.fitBounds(L.featureGroup(group).getBounds().pad(0.15), { maxZoom: 15 });

        document.querySelectorAll('.filter-btn').forEach(b => {
            const on = b.dataset.cat === current;
            b.className = 'filter-btn shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold border ' +
                (on ? 'bg-ink-900 text-white border-ink-900' : 'bg-white border-ink-200 hover:bg-ink-100');
        });
        document.getElementById('detail').classList.add('hidden');
    }

    function select(id, fly) {
        const p = PLACES.find(x => x.id === id);
        if (!p) return;
        if (fly) map.setView([p.lat, p.lng], 16);
        document.querySelectorAll('#list li').forEach(li => li.classList.toggle('bg-brand-50', +li.dataset.id === id));
        const detail = document.getElementById('detail');
        detail.innerHTML =
            '<button type="button" id="close-detail" class="absolute top-3 right-3 w-8 h-8 rounded-full hover:bg-ink-100 text-ink-500" aria-label="Cerrar">✕</button>' +
            '<div class="flex items-center gap-3 pr-8"><span class="w-12 h-12 shrink-0 rounded-2xl grid place-items-center text-2xl" style="background:' + p.color + '1f">' + p.emoji + '</span>' +
            '<div><p class="font-black text-lg leading-tight">' + esc(p.name) + '</p><p class="text-xs font-extrabold uppercase tracking-wide" style="color:' + p.color + '">' + esc(p.category_label) + '</p></div></div>' +
            '<p class="text-sm text-ink-700 mt-3">' + esc(p.description) + '</p>' +
            (p.services.length ? '<div class="flex flex-wrap gap-1.5 mt-3">' + p.services.map(s => '<span class="text-xs font-bold bg-ink-100 rounded-full px-2.5 py-1">' + esc(s) + '</span>').join('') + '</div>' : '') +
            '<div class="text-sm text-ink-700 mt-3 space-y-1">' +
                '<p>📍 ' + esc(p.address) + '</p>' +
                (p.schedule ? '<p>🕒 ' + esc(p.schedule) + '</p>' : '') +
                (p.phone ? '<p>📞 ' + esc(p.phone) + '</p>' : '') +
            '</div>' +
            '<div class="grid grid-cols-2 gap-2 mt-4">' +
                (p.whatsapp ? '<a target="_blank" rel="noopener" href="https://wa.me/' + p.whatsapp + '?text=' + encodeURIComponent('Hola, los encontré en PetRescue y quiero información.') + '" class="rounded-xl bg-[#25D366] text-white font-extrabold py-2.5 text-center text-sm">💬 WhatsApp</a>'
                            : (p.phone ? '<a href="tel:' + esc(p.phone) + '" class="rounded-xl bg-ink-900 text-white font-extrabold py-2.5 text-center text-sm">📞 Llamar</a>' : '')) +
                '<a target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination=' + p.lat + ',' + p.lng + '" class="rounded-xl border border-ink-200 font-extrabold py-2.5 text-center text-sm hover:bg-ink-50">🧭 Cómo llegar</a>' +
            '</div>';
        detail.classList.remove('hidden');
        document.getElementById('close-detail').addEventListener('click', () => detail.classList.add('hidden'));
    }

    document.querySelectorAll('.filter-btn').forEach(b => b.addEventListener('click', () => {
        current = b.dataset.cat;
        const url = new URL(window.location.href);
        current ? url.searchParams.set('categoria', current) : url.searchParams.delete('categoria');
        history.replaceState(null, '', url);
        render();
    }));
    document.getElementById('q').addEventListener('input', e => { search = e.target.value.trim().toLowerCase(); render(); });
    render();
})();
</script>
@endpush
