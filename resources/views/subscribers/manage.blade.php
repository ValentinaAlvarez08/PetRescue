@extends('layouts.app')

@section('title', 'Mis zonas de alerta — PetRescue')

@push('head')
    {{-- Enlace privado: que no lo indexen los buscadores. --}}
    <meta name="robots" content="noindex, nofollow">
@endpush

@php
    $canAdd = $zones->count() < $alerts['max_zones'];
    $mapZones = $zones->map(fn ($z) => [
        'lat' => (float) $z->latitude,
        'lng' => (float) $z->longitude,
        'radius' => (float) $z->radius_km,
        'label' => $z->label,
        'active' => $z->active,
    ])->values();
@endphp

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-black tracking-tight">🔔 Mis zonas de alerta</h1>
    <p class="text-ink-500 mt-2">
        Alertas para <strong class="text-ink-900">{{ $subscriber->email }}</strong>.
        Este enlace es privado: con él cualquiera puede ver y cambiar tus zonas, así que no lo compartas.
    </p>

    @if ($errors->any())
        <div class="mt-5 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($zones->isNotEmpty())
        <div class="mt-6 bg-white rounded-3xl shadow-soft border border-ink-100 p-3">
            <div id="zones-map" class="h-72 rounded-2xl"></div>
        </div>
    @endif

    <div class="mt-6 space-y-3">
        @forelse ($zones as $zone)
            <article class="bg-white rounded-3xl shadow-soft border border-ink-100 p-5" data-zone="{{ $zone->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-extrabold text-lg">{{ $zone->label }}</h2>
                        <p class="text-sm text-ink-500">Radio de {{ $zone->radius_label }} km</p>
                    </div>
                    <span class="text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full {{ $zone->active ? 'bg-emerald-100 text-emerald-700' : 'bg-ink-100 text-ink-500' }}">
                        {{ $zone->active ? '✓ Activa' : '⏸ Pausada' }}
                    </span>
                </div>

                <div class="mt-4 flex flex-col md:flex-row md:items-end gap-3">
                    <form action="{{ route('subscribers.zones.update', [$subscriber->manage_token, $zone->id]) }}" method="POST" class="flex-1 grid sm:grid-cols-[1fr_8rem_auto] gap-2 items-end">
                        @csrf
                        @method('PATCH')
                        <label class="text-xs font-bold text-ink-500">Nombre
                            <input type="text" name="label" value="{{ $zone->label }}" required maxlength="40" class="mt-1 w-full rounded-xl border border-ink-200 px-3 py-2 text-sm text-ink-900 font-normal">
                        </label>
                        <label class="text-xs font-bold text-ink-500">Radio (km)
                            <input type="number" name="radius_km" value="{{ $zone->radius_label }}" required min="{{ $alerts['radius_min'] }}" max="{{ $alerts['radius_max'] }}" step="1"
                                   class="mt-1 w-full rounded-xl border border-ink-200 px-3 py-2 text-sm text-ink-900 font-normal">
                        </label>
                        <button class="rounded-xl border border-ink-200 hover:bg-ink-50 font-bold px-4 py-2 text-sm">Guardar</button>
                    </form>

                    <div class="flex gap-2">
                        <form action="{{ route('subscribers.zones.update', [$subscriber->manage_token, $zone->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="active" value="{{ $zone->active ? 0 : 1 }}">
                            <button class="rounded-xl font-bold px-4 py-2 text-sm {{ $zone->active ? 'bg-ink-100 hover:bg-ink-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                {{ $zone->active ? 'Pausar' : 'Activar' }}
                            </button>
                        </form>
                        <form action="{{ route('subscribers.zones.destroy', [$subscriber->manage_token, $zone->id]) }}" method="POST"
                              onsubmit="return confirm({{ json_encode('¿Eliminar la zona «'.$zone->label.'»?', JSON_UNESCAPED_UNICODE) }})">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50 font-bold px-4 py-2 text-sm">Eliminar</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-3xl bg-white border border-ink-100 p-6 text-ink-500">No tienes zonas guardadas. Agrega una para empezar a recibir alertas.</p>
        @endforelse
    </div>

    <section class="mt-8">
        @if ($canAdd)
            <h2 class="text-xl font-black mb-3">➕ Agregar otra zona</h2>
            <form action="{{ route('subscribers.zones.store', $subscriber->manage_token) }}" method="POST" class="bg-white rounded-3xl shadow-soft border border-ink-100 p-5 sm:p-8 space-y-5">
                @csrf
                @include('subscribers._zone_fields')
                <button type="submit" class="w-full sm:w-auto rounded-xl bg-ink-900 hover:bg-ink-700 text-white font-extrabold px-6 py-3">Guardar zona</button>
            </form>
        @else
            <p class="rounded-2xl bg-brand-50 border border-brand-200 text-brand-900 p-4 text-sm font-semibold">
                Ya tienes {{ $alerts['max_zones'] }} zonas, el máximo por correo. Elimina una para agregar otra.
            </p>
        @endif
    </section>
</div>
@endsection

@if ($zones->isNotEmpty())
    @push('scripts')
    <script>
    (function () {
        const zones = @json($mapZones);
        const map = L.map('zones-map', { scrollWheelZoom: false });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        const group = L.featureGroup().addTo(map);
        zones.forEach(z => {
            const color = z.active ? '#f97316' : '#78716c';
            L.circle([z.lat, z.lng], { radius: z.radius * 1000, color: color, weight: 2, fillOpacity: z.active ? 0.12 : 0.05, dashArray: z.active ? null : '6 6' })
                .bindTooltip(z.label + (z.active ? '' : ' (pausada)'), { permanent: true, direction: 'center', className: 'font-bold' })
                .addTo(group);
        });
        map.fitBounds(group.getBounds(), { padding: [20, 20] });
    })();
    </script>
    @endpush
@endif
