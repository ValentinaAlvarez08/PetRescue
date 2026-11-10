@php $lost = $report->type === 'perdida'; @endphp
<a href="{{ route('reports.show', $report) }}"
   class="tilt group relative block bg-white rounded-3xl border border-ink-100 shadow-soft overflow-hidden hover:shadow-2xl {{ $lost ? 'hover:shadow-rose-500/15 hover:border-rose-200' : 'hover:shadow-emerald-600/15 hover:border-emerald-200' }}">
    <div class="relative h-44 bg-ink-100 overflow-hidden">
        @if ($report->photo_path)
            <img src="{{ $report->photo_url }}" alt="" class="w-full h-full object-cover group-hover:scale-110 duration-700" loading="lazy">
        @else
            <div class="w-full h-full grid place-items-center text-6xl bg-gradient-to-br {{ $lost ? 'from-rose-50 to-brand-50' : 'from-emerald-50 to-sky-50' }}">
                <span class="group-hover:scale-125 group-hover:-rotate-6 transition duration-500">{{ $report->species_emoji }}</span>
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-ink-900/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-500"></div>
        <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-md {{ $lost ? 'bg-rose-500 text-white' : 'bg-emerald-600 text-white' }}">
            <span class="pulse-dot text-white" style="width:6px;height:6px"></span>
            {{ $lost ? 'Perdida' : 'Encontrada' }}
        </span>
        <span class="absolute bottom-3 right-3 rounded-full bg-white/95 text-ink-900 text-xs font-extrabold px-3 py-1.5 shadow-md translate-y-3 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition duration-300">
            Ver reporte →
        </span>
    </div>
    <div class="p-4">
        <h3 class="font-extrabold text-lg leading-tight group-hover:{{ $lost ? 'text-rose-600' : 'text-emerald-700' }} transition-colors">{{ $report->display_name }}</h3>
        <p class="text-sm text-ink-500">{{ $report->species_emoji }} {{ $report->species_label }}{{ $report->breed ? ' · '.$report->breed : '' }}</p>
        <p class="text-sm text-ink-700 mt-2 line-clamp-2">{{ $report->description }}</p>
        <p class="text-xs text-ink-500 mt-3 pt-3 border-t border-dashed border-ink-100 flex items-center justify-between gap-2">
            <span class="truncate">📍 {{ $report->location_reference ?: 'Ver en el mapa' }}</span>
            <span class="shrink-0 rounded-full bg-ink-50 px-2 py-0.5 font-bold">
                @isset($report->distance_km) a {{ number_format($report->distance_km, 1) }} km @else {{ $report->created_at->diffForHumans() }} @endisset
            </span>
        </p>
    </div>
</a>
