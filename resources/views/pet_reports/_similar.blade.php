{{--
    Sprint 4 — HU-05: reportes del tipo contrario que podrían ser la misma mascota.
    Todos los enlaces son públicos (nunca el enlace privado de gestión).
--}}
@php $lost = $report->type === 'perdida'; @endphp

<section class="mt-8" id="similares" data-role="similar-reports">
    <div class="mb-4">
        <p class="text-sm font-extrabold uppercase tracking-wider {{ $lost ? 'text-emerald-600' : 'text-rose-600' }}">
            {{ $canManage ? 'Posibles coincidencias con tu reporte' : 'Reportes similares' }}
        </p>
        <h2 class="text-2xl font-black">
            {{ $lost ? 'Mascotas encontradas que se parecen' : 'Mascotas perdidas que se parecen' }}
        </h2>
        <p class="text-ink-500 text-sm mt-1">
            Misma especie, a menos de {{ config('pets.matching.radius_km') }} km y con fechas que coinciden. Revisa las fotos y las señas antes de contactar.
        </p>
    </div>

    @if ($similar->isEmpty())
        <div class="rounded-3xl border-2 border-dashed border-ink-200 bg-white p-6 text-center text-ink-500">
            <p class="font-bold text-ink-700">Por ahora no hay reportes parecidos.</p>
            @if ($canManage)
                <p class="text-sm mt-1">
                    Esta lista se actualiza sola.
                    {{ $report->contact_email ? 'Te escribiremos a '.$report->contact_email.' si aparece uno.' : 'Si editas el reporte y agregas tu correo, te avisaremos cuando aparezca uno.' }}
                </p>
            @endif
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($similar as $match)
                <a href="{{ route('reports.show', $match) }}" data-role="similar-report"
                   class="group block bg-white rounded-3xl border border-ink-100 shadow-soft overflow-hidden hover:-translate-y-0.5 hover:shadow-lg transition">
                    <div class="relative h-40 bg-ink-100">
                        @if ($match->photo_url)
                            <img src="{{ $match->photo_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full grid place-items-center text-6xl">{{ $match->species_emoji }}</div>
                        @endif
                        <span class="absolute top-3 right-3 text-xs font-black px-2.5 py-1 rounded-full bg-white/95 text-ink-900 shadow">
                            {{ $match->match_score }}% parecido
                        </span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-extrabold leading-tight">{{ $match->display_name }}</h3>
                        <p class="text-xs text-ink-500">{{ $match->created_at->locale('es')->diffForHumans() }}{{ $match->location_reference ? ' · '.$match->location_reference : '' }}</p>
                        <ul class="flex flex-wrap gap-1 mt-2">
                            @foreach ($match->match_reasons as $reason)
                                <li class="text-[11px] font-bold bg-ink-100 text-ink-700 rounded-full px-2 py-0.5">{{ $reason }}</li>
                            @endforeach
                        </ul>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
