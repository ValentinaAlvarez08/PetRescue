@extends('layouts.app')

@section('title', 'Estadísticas de reencuentros — PetRescue')

@php
    $monthMax = max(1, collect($byMonth)->max('total'));
    $monthPeak = collect($byMonth)->search(fn ($m) => $m['total'] === collect($byMonth)->max('total'));
    $speciesMax = max(1, collect($bySpecies)->max('total'));
    $reunitedRate = $totals['reports'] ? round($totals['reunited'] * 100 / $totals['reports']) : 0;
@endphp

@section('content')
<div class="space-y-10">
    <header>
        <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Estadísticas</p>
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Así nos ayudamos entre todos</h1>
        <p class="text-ink-500 mt-2 max-w-2xl">Cada reporte, cada vez que alguien comparte y cada aviso suma. Estos son los números de la comunidad PetRescue.</p>
    </header>

    {{-- HU-12 (CID 1): totales --}}
    <section class="grid sm:grid-cols-3 gap-3" aria-label="Totales">
        @foreach ([
            ['n' => $totals['reports'], 'l' => 'reportes publicados', 'c' => 'text-ink-900', 'k' => 'reports'],
            ['n' => $totals['reunited'], 'l' => 'mascotas reunidas con su familia', 'c' => 'text-emerald-700', 'k' => 'reunited'],
            ['n' => $totals['active'], 'l' => 'reportes activos buscando ayuda', 'c' => 'text-rose-600', 'k' => 'active'],
        ] as $s)
            <div class="rounded-3xl bg-white border border-ink-100 shadow-soft p-6" data-stat="{{ $s['k'] }}">
                <p class="text-4xl sm:text-5xl font-black {{ $s['c'] }}">{{ number_format($s['n'], 0, ',', '.') }}</p>
                <p class="text-sm font-bold text-ink-500 mt-1">{{ $s['l'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- HU-12 (CID 2 y 4): historias de éxito o invitación a reportar --}}
    @if ($stories->isNotEmpty())
        <section id="historias">
            <div class="flex items-end justify-between gap-4 mb-5">
                <div>
                    <p class="text-sm font-extrabold uppercase tracking-wider text-emerald-700">Historias de éxito</p>
                    <h2 class="text-2xl sm:text-3xl font-black">Ya volvieron a casa 🎉</h2>
                    <p class="text-ink-500 mt-1">{{ $reunitedRate }}% de los reportes terminaron en reencuentro.</p>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($stories as $story)
                    <a href="{{ route('reports.show', $story) }}" data-story
                       class="group block bg-white rounded-3xl border border-ink-100 shadow-soft overflow-hidden hover:-translate-y-0.5 hover:shadow-lg transition">
                        <div class="relative h-48 bg-ink-100">
                            @if ($story->photo_url)
                                <img src="{{ $story->photo_url }}" alt="Foto de {{ $story->display_name }}" class="w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="w-full h-full grid place-items-center text-6xl">{{ $story->species_emoji }}</div>
                            @endif
                            <span class="absolute top-3 left-3 text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-600 text-white">¡Reunido!</span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-extrabold text-lg leading-tight">{{ $story->display_name }}</h3>
                            <p class="text-sm text-ink-500">{{ $story->species_emoji }} {{ $story->species_label }}{{ $story->breed ? ' · '.$story->breed : '' }}</p>
                            <p class="text-xs text-ink-500 mt-3">
                                {{ $story->type === 'perdida' ? 'Se había perdido' : 'Fue encontrada' }}{{ $story->location_reference ? ' en '.$story->location_reference : '' }}
                                · volvió a casa {{ ($story->reunited_at ?? $story->updated_at)->diffForHumans() }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @else
        <section class="rounded-3xl bg-emerald-50 border border-emerald-200 p-8 text-center" data-empty-stories>
            <p class="text-4xl">🏡</p>
            <h2 class="text-2xl font-black mt-2">Todavía no hay reencuentros registrados</h2>
            <p class="text-ink-700 mt-2 max-w-xl mx-auto">¡El primero puede ser gracias a ti! Si perdiste o encontraste una mascota, repórtala para que la comunidad te ayude a buscar a su familia.</p>
            <div class="mt-5 flex flex-col sm:flex-row gap-2 justify-center">
                <a href="{{ route('reports.create', 'perdida') }}" class="rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-600 px-5 py-3">Perdí mi mascota</a>
                <a href="{{ route('reports.create', 'encontrada') }}" class="rounded-xl font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-5 py-3">Encontré una</a>
            </div>
        </section>
    @endif

    {{-- HU-12 (CID 3): gráficos por mes y por especie --}}
    <section class="grid lg:grid-cols-[1.4fr_1fr] gap-4">
        <figure class="bg-white rounded-3xl border border-ink-100 shadow-soft p-6" data-chart="month">
            <figcaption>
                <h2 class="font-extrabold text-lg">Reportes por mes</h2>
                <p class="text-sm text-ink-500">Últimos 12 meses</p>
            </figcaption>

            <div class="mt-6 h-52 flex items-end gap-[2px] border-b border-ink-200" role="img"
                 aria-label="Reportes por mes: {{ collect($byMonth)->map(fn ($m) => $m['label'].' '.$m['year'].' '.$m['total'])->implode(', ') }}">
                @foreach ($byMonth as $i => $month)
                    @php $showValue = $month['total'] > 0 && ($i === $monthPeak || $loop->last); @endphp
                    <div class="group relative flex-1 h-full flex flex-col justify-end items-center" tabindex="0">
                        @if ($showValue)
                            <span class="text-xs font-extrabold text-ink-700 mb-1">{{ $month['total'] }}</span>
                        @endif
                        <div class="w-full max-w-[2.5rem] rounded-t bg-[#ea580c] group-hover:bg-[#c2410c] transition-colors"
                             style="height: {{ $month['total'] / $monthMax * 88 }}%"></div>
                        <span class="pointer-events-none absolute bottom-full mb-1 hidden group-hover:block group-focus:block whitespace-nowrap z-10 rounded-lg bg-ink-900 text-white text-xs font-bold px-2 py-1">
                            {{ ucfirst($month['label']) }} {{ $month['year'] }}: {{ $month['total'] }} {{ $month['total'] === 1 ? 'reporte' : 'reportes' }}
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-[2px] mt-2" aria-hidden="true">
                @foreach ($byMonth as $month)
                    <span class="flex-1 text-center text-[11px] font-semibold text-ink-500 leading-tight">
                        {{ $month['label'] }}@if ($loop->first || $month['label'] === 'ene')<br>{{ $month['year'] }}@endif
                    </span>
                @endforeach
            </div>
        </figure>

        <figure class="bg-white rounded-3xl border border-ink-100 shadow-soft p-6" data-chart="species">
            <figcaption>
                <h2 class="font-extrabold text-lg">Reportes por especie</h2>
                <p class="text-sm text-ink-500">Desde que empezó PetRescue</p>
            </figcaption>

            @if (empty($bySpecies))
                <p class="mt-6 text-sm text-ink-500">Aún no hay reportes.</p>
            @else
                <ul class="mt-6 space-y-3">
                    @foreach ($bySpecies as $row)
                        <li class="grid grid-cols-[7.5rem_1fr_2.5rem] items-center gap-3 text-sm"
                            title="{{ $row['label'] }}: {{ $row['total'] }} {{ $row['total'] === 1 ? 'reporte' : 'reportes' }}">
                            <span class="font-bold truncate">{{ $row['emoji'] }} {{ $row['label'] }}</span>
                            <span class="h-5 rounded bg-ink-100 overflow-hidden">
                                <span class="block h-full rounded bg-[#ea580c]" style="width: {{ $row['total'] / $speciesMax * 100 }}%"></span>
                            </span>
                            <span class="font-extrabold text-ink-700 text-right">{{ $row['total'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </figure>
    </section>

    <details class="bg-white rounded-3xl border border-ink-100 shadow-soft p-6">
        <summary class="font-bold cursor-pointer">Ver los datos en tabla</summary>
        <div class="mt-4 grid md:grid-cols-2 gap-6 text-sm">
            <table class="w-full">
                <thead><tr class="text-left text-ink-500"><th class="py-1">Mes</th><th class="py-1 text-right">Reportes</th></tr></thead>
                <tbody>
                    @foreach ($byMonth as $month)
                        <tr class="border-t border-ink-100"><td class="py-1">{{ ucfirst($month['label']) }} {{ $month['year'] }}</td><td class="py-1 text-right font-bold">{{ $month['total'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <table class="w-full self-start">
                <thead><tr class="text-left text-ink-500"><th class="py-1">Especie</th><th class="py-1 text-right">Reportes</th></tr></thead>
                <tbody>
                    @foreach ($bySpecies as $row)
                        <tr class="border-t border-ink-100"><td class="py-1">{{ $row['label'] }}</td><td class="py-1 text-right font-bold">{{ $row['total'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</div>
@endsection
