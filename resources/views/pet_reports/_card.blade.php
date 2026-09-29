@php $lost = $report->type === 'perdida'; @endphp
<a href="{{ route('reports.show', $report) }}"
   class="group block bg-white rounded-3xl border border-ink-100 shadow-soft overflow-hidden hover:-translate-y-0.5 hover:shadow-lg transition">
    <div class="relative h-44 bg-ink-100">
        @if ($report->photo_path)
            <img src="{{ Storage::url($report->photo_path) }}" alt="" class="w-full h-full object-cover" loading="lazy">
        @else
            <div class="w-full h-full grid place-items-center text-6xl">{{ $report->species_emoji }}</div>
        @endif
        <span class="absolute top-3 left-3 text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full {{ $lost ? 'bg-rose-500 text-white' : 'bg-emerald-600 text-white' }}">
            {{ $lost ? 'Perdida' : 'Encontrada' }}
        </span>
    </div>
    <div class="p-4">
        <h3 class="font-extrabold text-lg leading-tight">{{ $report->display_name }}</h3>
        <p class="text-sm text-ink-500">{{ $report->species_emoji }} {{ $report->species_label }}{{ $report->breed ? ' · '.$report->breed : '' }}</p>
        <p class="text-sm text-ink-700 mt-2 line-clamp-2">{{ $report->description }}</p>
        <p class="text-xs text-ink-500 mt-3 flex items-center justify-between gap-2">
            <span class="truncate">📍 {{ $report->location_reference ?: 'Ver en el mapa' }}</span>
            <span class="shrink-0">
                @isset($report->distance_km) a {{ number_format($report->distance_km, 1) }} km @else {{ $report->created_at->diffForHumans() }} @endisset
            </span>
        </p>
    </div>
</a>
