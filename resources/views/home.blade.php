@extends('layouts.app')

@section('title', 'PetRescue — La comunidad de las mascotas')
@section('main_class', 'w-full')

@section('content')
{{-- ============ Bienvenida ============ --}}
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50 via-white/60 to-ink-50">
    <div class="blob absolute -top-24 -right-24 w-[28rem] h-[28rem] bg-brand-200/50 blur-3xl"></div>
    <div class="blob absolute top-40 -left-24 w-80 h-80 bg-emerald-200/50 blur-3xl" style="animation-delay:-6s"></div>
    <div class="blob absolute bottom-0 left-1/2 w-72 h-72 bg-rose-200/40 blur-3xl" style="animation-delay:-12s"></div>

    {{-- Huellitas decorativas flotando --}}
    <div class="pointer-events-none absolute inset-0 hidden md:block" aria-hidden="true">
        <span class="float absolute top-16 left-[46%] text-3xl opacity-20" style="--r:-20deg">🐾</span>
        <span class="float absolute bottom-16 left-[8%] text-2xl opacity-20" style="--r:15deg;animation-delay:-2s">🐾</span>
        <span class="float absolute top-28 right-[6%] text-2xl opacity-20" style="--r:30deg;animation-delay:-4s">🐾</span>
    </div>

    <div class="relative max-w-6xl mx-auto px-4 pt-14 pb-20 grid lg:grid-cols-[1.25fr_1fr] gap-12 items-center">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full bg-white border border-brand-100 px-3 py-1.5 text-sm font-bold text-brand-700 shadow-soft">
                <span class="pulse-dot text-emerald-500"></span>
                Hecho por y para la comunidad de Pasto
            </p>
            <h1 class="mt-5 text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.08]">
                Bienvenido a la casa de
                <span class="rotator pb-1">
                    <span class="text-gradient is-current">todas las mascotas</span>
                    <span class="text-gradient">los perritos</span>
                    <span class="text-gradient">los gatitos</span>
                    <span class="text-gradient">los rescatados</span>
                </span>
            </h1>
            <p class="mt-5 text-lg text-ink-700 max-w-xl leading-relaxed">
                Aquí nos ayudamos a encontrar a los que se pierden, compartimos lo que aprendemos cuidándolos
                y encontramos los mejores lugares para ellos. Sin cuentas, sin complicaciones.
            </p>
            <div class="mt-8 grid sm:grid-cols-2 gap-4 max-w-xl">
                <a href="{{ route('reports.create', 'perdida') }}"
                   class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-500 to-rose-600 text-white p-5 shadow-xl shadow-rose-500/25 hover:-translate-y-1 hover:shadow-2xl hover:shadow-rose-500/40 transition duration-300">
                    <span class="absolute -right-4 -bottom-6 text-8xl opacity-15 group-hover:scale-110 group-hover:-rotate-12 transition duration-500">🐾</span>
                    <span class="text-3xl inline-block group-hover:animate-bounce">😿</span>
                    <p class="font-black text-lg mt-1">Perdí mi mascota</p>
                    <p class="text-sm text-white/85">Publica la alerta en 3 pasos <span class="inline-block group-hover:translate-x-1 transition">→</span></p>
                </a>
                <a href="{{ route('reports.create', 'encontrada') }}"
                   class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white p-5 shadow-xl shadow-emerald-600/25 hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-600/40 transition duration-300">
                    <span class="absolute -right-4 -bottom-6 text-8xl opacity-15 group-hover:scale-110 group-hover:rotate-12 transition duration-500">🏠</span>
                    <span class="text-3xl inline-block group-hover:animate-bounce">🤗</span>
                    <p class="font-black text-lg mt-1">Encontré una mascota</p>
                    <p class="text-sm text-white/85">Ayúdala a volver a casa <span class="inline-block group-hover:translate-x-1 transition">→</span></p>
                </a>
            </div>
            <a href="{{ route('reports.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-ink-700 hover:text-brand-700 group">
                <span class="w-8 h-8 rounded-full bg-white shadow-soft grid place-items-center group-hover:bg-brand-500 transition">🎯</span>
                O busca en el mapa los casos cerca de ti
            </a>
        </div>

        <div class="relative">
            <div class="absolute -inset-4 bg-gradient-to-br from-brand-300/30 to-rose-300/30 rounded-[2.5rem] rotate-3 blur-sm hidden lg:block"></div>
            <div class="relative rounded-[2rem] bg-white/80 backdrop-blur-xl border border-white shadow-2xl p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-black text-ink-900">La comunidad hoy</p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 rounded-full px-2.5 py-1">
                        <span class="pulse-dot text-emerald-500" style="width:6px;height:6px"></span> En vivo
                    </span>
                </div>
                <div class="grid grid-cols-3 lg:grid-cols-1 gap-3">
                    @foreach ([
                        ['n' => $stats['active'], 'l' => 'casos activos buscando hogar', 'c' => 'text-rose-600', 'bg' => 'from-rose-50', 'e' => '🔎'],
                        ['n' => $stats['reunited'], 'l' => 'mascotas ya volvieron a casa', 'c' => 'text-emerald-600', 'bg' => 'from-emerald-50', 'e' => '🏡'],
                        ['n' => $stats['businesses'], 'l' => 'servicios en el directorio', 'c' => 'text-brand-600', 'bg' => 'from-brand-50', 'e' => '📍'],
                    ] as $s)
                        <div class="group rounded-2xl bg-gradient-to-r {{ $s['bg'] }} to-white border border-ink-100 p-4 lg:flex lg:items-center lg:gap-4 hover:scale-[1.03] hover:shadow-soft transition">
                            <span class="hidden lg:grid w-12 h-12 shrink-0 rounded-2xl bg-white shadow-soft place-items-center text-2xl group-hover:rotate-12 transition">{{ $s['e'] }}</span>
                            <p class="text-3xl lg:text-4xl font-black tabular-nums {{ $s['c'] }}" data-count="{{ $s['n'] }}">{{ $s['n'] }}</p>
                            <p class="text-xs lg:text-sm font-bold text-ink-500">{{ $s['l'] }}</p>
                        </div>
                    @endforeach
                </div>
                {{-- Sprint 4 — HU-12 --}}
                <a href="{{ route('stats.index') }}" class="mt-4 flex items-center justify-center gap-2 rounded-2xl bg-ink-900 hover:bg-ink-700 text-white text-sm font-bold py-3 transition">
                    Ver estadísticas e historias de éxito →
                </a>
            </div>
            <div class="float absolute -bottom-6 -left-6 hidden lg:grid w-16 h-16 rounded-2xl bg-white shadow-xl place-items-center text-3xl" style="--r:-8deg">🐶</div>
            <div class="float absolute -bottom-6 -right-4 hidden lg:grid w-16 h-16 rounded-2xl bg-white shadow-xl place-items-center text-3xl" style="--r:8deg;animation-delay:-3s">🐱</div>
        </div>
    </div>
</section>

{{-- ============ Cinta de valores ============ --}}
<div class="bg-ink-900 text-white py-3 overflow-hidden marquee" aria-hidden="true">
    <div class="marquee-track text-sm font-bold whitespace-nowrap">
        @for ($i = 0; $i < 2; $i++)
            @foreach (['🐾 Sin crear cuenta', '📍 Mapa de reportes', '🔔 Alertas por zona', '🤝 Hecho por vecinos', '🪪 Perfil con QR', '🩺 Veterinarias y servicios', '📖 Guías paso a paso', '❤️ 100% gratis'] as $item)
                <span class="px-5 opacity-80">{{ $item }}</span><span class="text-brand-400">✦</span>
            @endforeach
        @endfor
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-12 space-y-20">

    {{-- ============ Cómo funciona ============ --}}
    <section>
        <div class="text-center max-w-2xl mx-auto mb-10">
            <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Cómo funciona</p>
            <h2 class="text-2xl sm:text-3xl font-black">De la alerta al reencuentro, en tres pasos</h2>
        </div>
        <div class="relative grid md:grid-cols-3 gap-6">
            <div class="hidden md:block absolute top-16 left-[16%] right-[16%] border-t-2 border-dashed border-brand-200" aria-hidden="true"></div>
            @foreach ([
                ['e' => '📝', 't' => 'Publica el reporte', 'd' => 'Foto, señas y el punto en el mapa. Toma menos de dos minutos y no necesitas registrarte.'],
                ['e' => '📣', 't' => 'La comunidad se entera', 'd' => 'Avisamos a los vecinos suscritos en esa zona y te sugerimos reportes parecidos.'],
                ['e' => '🏡', 't' => '¡De vuelta a casa!', 'd' => 'Marca el reporte como reunido y tu historia inspira a otros en las estadísticas.'],
            ] as $i => $step)
                <div class="group relative text-center rounded-3xl bg-white border border-ink-100 shadow-soft p-6 pt-8 hover:-translate-y-1 hover:shadow-xl transition duration-300">
                    <div class="relative mx-auto w-20 h-20 rounded-3xl bg-gradient-to-br from-brand-100 to-rose-100 grid place-items-center text-4xl group-hover:scale-110 group-hover:rotate-6 transition duration-500">
                        {{ $step['e'] }}
                        <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-ink-900 text-white text-xs font-black grid place-items-center ring-4 ring-white">{{ $i + 1 }}</span>
                    </div>
                    <h3 class="font-black text-lg mt-5">{{ $step['t'] }}</h3>
                    <p class="text-sm text-ink-500 mt-2 leading-relaxed">{{ $step['d'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ Directorio ============ --}}
    <section>
        <div class="flex items-end justify-between gap-4 mb-5">
            <div>
                <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Directorio</p>
                <h2 class="text-2xl sm:text-3xl font-black">Todo lo que tu mascota necesita, cerca de ti</h2>
            </div>
            <a href="{{ route('directory.index') }}" class="hidden sm:inline font-bold text-brand-700 hover:underline whitespace-nowrap">Ver el mapa →</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @foreach ($directory as $key => $cat)
                <a href="{{ route('directory.index', ['categoria' => $key]) }}"
                   class="tilt group relative rounded-3xl bg-white border border-ink-100 shadow-soft p-5 hover:shadow-xl overflow-hidden">
                    <span class="absolute -right-8 -top-8 w-24 h-24 rounded-full opacity-0 group-hover:opacity-100 scale-50 group-hover:scale-100 transition duration-500" style="background: {{ $cat['color'] }}1a"></span>
                    <span class="relative w-12 h-12 rounded-2xl grid place-items-center text-2xl group-hover:scale-110 group-hover:-rotate-6 transition duration-300" style="background: {{ $cat['color'] }}1f">{{ $cat['emoji'] }}</span>
                    <p class="font-extrabold mt-3 leading-tight">{{ $cat['short'] }}</p>
                    <p class="text-xs text-ink-500 mt-1">{{ $cat['blurb'] }}</p>
                    <p class="text-xs font-bold mt-3" style="color: {{ $cat['color'] }}">{{ $countsByCategory[$key] ?? 0 }} lugares <span class="inline-block group-hover:translate-x-1 transition">→</span></p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============ Comunidad ============ --}}
    <section id="comunidad" class="scroll-mt-24">
        <div class="mb-5">
            <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Comunidad</p>
            <h2 class="text-2xl sm:text-3xl font-black">Hablemos de lo que vivimos con ellos</h2>
            <p class="text-ink-500 mt-1">Espacios para preguntar, contar tu experiencia y aprender de otros dueños.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($topics as $slug => $topic)
                <a href="{{ route('community.show', $slug) }}"
                   class="group flex gap-4 rounded-3xl bg-white border border-ink-100 shadow-soft p-5 hover:border-brand-200 hover:bg-brand-50/50 hover:-translate-y-0.5 hover:shadow-lg transition duration-300">
                    <span class="w-12 h-12 shrink-0 rounded-2xl bg-ink-100 grid place-items-center text-2xl group-hover:bg-brand-100 group-hover:scale-110 transition duration-300">{{ $topic['emoji'] }}</span>
                    <span>
                        <span class="block font-extrabold group-hover:text-brand-700 transition-colors">{{ $topic['label'] }}</span>
                        <span class="block text-sm text-ink-500 mt-0.5">{{ $topic['blurb'] }}</span>
                        {{-- Sprint 3 — HU-17: publicaciones del tema --}}
                        @php $count = (int) ($postsByTopic[$slug] ?? 0); @endphp
                        <span class="inline-block mt-2 text-xs font-extrabold text-brand-700 bg-brand-50 rounded-full px-2.5 py-0.5" data-role="topic-count">
                            {{ $count }} {{ $count === 1 ? 'publicación' : 'publicaciones' }}
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============ Guías y perfil digital (Sprint 4 — HU-21 / HU-10) ============ --}}
    <section class="grid lg:grid-cols-[1.4fr_1fr] gap-4">
        <div class="rounded-3xl bg-white border border-ink-100 shadow-soft p-6 sm:p-8">
            <p class="text-sm font-extrabold uppercase tracking-wider text-brand-600">Guías rápidas</p>
            <h2 class="text-2xl font-black">Qué hacer, paso a paso</h2>
            <div class="mt-4 grid sm:grid-cols-2 gap-2">
                @foreach (collect(config('guides.guides'))->take(4) as $slug => $guide)
                    <a href="{{ route('guides.show', $slug) }}" class="group flex items-center gap-3 rounded-2xl bg-ink-50 hover:bg-brand-50 hover:translate-x-1 p-3 transition">
                        <span class="text-2xl group-hover:scale-125 transition">{{ $guide['emoji'] }}</span>
                        <span class="text-sm font-bold leading-tight">{{ $guide['title'] }}</span>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('guides.index') }}" class="inline-block mt-4 font-bold text-brand-700 hover:underline">Ver todas las guías →</a>
        </div>
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 via-rose-500 to-brand-600 bg-animated text-white p-6 sm:p-8 flex flex-col shadow-xl shadow-brand-500/25">
            <span class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10 group-hover:scale-125 transition duration-700"></span>
            <span class="absolute -left-6 -bottom-12 w-32 h-32 rounded-full bg-white/10 group-hover:scale-125 transition duration-700"></span>
            <p class="relative text-4xl">🪪</p>
            <h2 class="relative text-2xl font-black mt-2">Crea su perfil digital</h2>
            <p class="relative text-white/85 mt-1 flex-1">Foto, señas y datos de salud en un solo lugar, con un QR para la placa del collar. Si se pierde, el reporte se crea con un clic.</p>
            <a href="{{ route('profiles.create') }}" class="relative mt-4 rounded-2xl bg-white text-ink-900 hover:bg-ink-50 hover:scale-[1.02] font-extrabold px-5 py-3 text-center shadow-lg transition">Crear perfil gratis</a>
        </div>
    </section>

    {{-- ============ Reportes recientes ============ --}}
    <section>
        <div class="flex items-end justify-between gap-4 mb-5">
            <div>
                <p class="text-sm font-extrabold uppercase tracking-wider text-rose-600">Ayúdalos a volver</p>
                <h2 class="text-2xl sm:text-3xl font-black">Reportes recientes</h2>
            </div>
            <a href="{{ route('reports.index') }}" class="font-bold text-brand-700 hover:underline whitespace-nowrap">Ver todos →</a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($latestReports as $report)
                @include('pet_reports._card', ['report' => $report])
            @empty
                <div class="col-span-full text-center rounded-3xl border-2 border-dashed border-ink-200 p-10">
                    <p class="text-5xl float inline-block">🐾</p>
                    <p class="font-bold mt-2 text-ink-700">Todavía no hay reportes activos. ¡Ojalá siga así!</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- ============ Avisos ============ --}}
    <section class="relative overflow-hidden rounded-3xl bg-ink-900 text-white p-8 sm:p-10 grid md:grid-cols-[1fr_auto] gap-6 items-center">
        <div class="blob absolute -right-20 -top-24 w-72 h-72 bg-brand-500/30 blur-3xl"></div>
        <div class="blob absolute -left-10 -bottom-28 w-64 h-64 bg-rose-500/20 blur-3xl" style="animation-delay:-8s"></div>
        <div class="relative">
            <h2 class="text-2xl sm:text-3xl font-black">Sé los ojos de tu barrio 👀</h2>
            <p class="text-white/70 mt-2 max-w-2xl">Recibe un correo cuando alguien reporte una mascota perdida o encontrada cerca de tu casa, tu trabajo o tu finca. Sin crear cuenta y te das de baja cuando quieras.</p>
        </div>
        <a href="{{ route('subscribers.create') }}" class="relative rounded-2xl bg-brand-500 hover:bg-brand-600 hover:scale-105 font-extrabold px-6 py-4 text-center shadow-xl shadow-brand-500/30 transition">🔔 Activar alertas</a>
    </section>
</div>
@endsection
