@extends('layouts.app')

@section('title', 'PetRescue — La comunidad de las mascotas')
@section('main_class', 'w-full')

@section('content')
{{-- ============ Bienvenida ============ --}}
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50 to-ink-50">
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-brand-200/40 blur-3xl"></div>
    <div class="absolute top-40 -left-24 w-72 h-72 rounded-full bg-emerald-200/40 blur-3xl"></div>

    <div class="relative max-w-6xl mx-auto px-4 pt-14 pb-16 grid lg:grid-cols-[1.25fr_1fr] gap-10 items-center">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full bg-white border border-brand-100 px-3 py-1 text-sm font-bold text-brand-700 shadow-soft">
                🐾 Hecho por y para la comunidad de Pasto
            </p>
            <h1 class="mt-5 text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.05]">
                Bienvenido a la casa de <span class="text-brand-600">todas las mascotas</span>
            </h1>
            <p class="mt-5 text-lg text-ink-700 max-w-xl">
                Aquí nos ayudamos a encontrar a los que se pierden, compartimos lo que aprendemos cuidándolos
                y encontramos los mejores lugares para ellos. Sin cuentas, sin complicaciones.
            </p>
            <div class="mt-8 grid sm:grid-cols-2 gap-3 max-w-xl">
                <a href="{{ route('reports.create', 'perdida') }}"
                   class="group rounded-2xl bg-rose-500 hover:bg-rose-600 text-white p-5 shadow-soft transition">
                    <span class="text-2xl">😿</span>
                    <p class="font-black text-lg mt-1">Perdí mi mascota</p>
                    <p class="text-sm text-white/85">Publica la alerta en 3 pasos →</p>
                </a>
                <a href="{{ route('reports.create', 'encontrada') }}"
                   class="group rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white p-5 shadow-soft transition">
                    <span class="text-2xl">🤗</span>
                    <p class="font-black text-lg mt-1">Encontré una mascota</p>
                    <p class="text-sm text-white/85">Ayúdala a volver a casa →</p>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-3 lg:grid-cols-1 gap-3">
            @foreach ([
                ['n' => $stats['active'], 'l' => 'casos activos buscando hogar', 'c' => 'text-rose-600'],
                ['n' => $stats['reunited'], 'l' => 'mascotas ya volvieron a casa', 'c' => 'text-emerald-600'],
                ['n' => $stats['businesses'], 'l' => 'servicios en el directorio', 'c' => 'text-brand-600'],
            ] as $s)
                <div class="rounded-2xl bg-white/80 backdrop-blur border border-white p-4 lg:p-5 shadow-soft lg:flex lg:items-center lg:gap-4">
                    <p class="text-3xl lg:text-4xl font-black {{ $s['c'] }}">{{ $s['n'] }}</p>
                    <p class="text-xs lg:text-sm font-bold text-ink-500">{{ $s['l'] }}</p>
                </div>
            @endforeach
            {{-- Sprint 4 — HU-12 --}}
            <a href="{{ route('stats.index') }}" class="col-span-3 lg:col-span-1 text-center lg:text-left text-sm font-bold text-brand-700 hover:underline">Ver estadísticas e historias de éxito →</a>
        </div>
    </div>
</section>

<div class="max-w-6xl mx-auto px-4 py-12 space-y-16">

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
                   class="group rounded-3xl bg-white border border-ink-100 shadow-soft p-5 hover:-translate-y-0.5 hover:shadow-lg transition">
                    <span class="w-12 h-12 rounded-2xl grid place-items-center text-2xl" style="background: {{ $cat['color'] }}1f">{{ $cat['emoji'] }}</span>
                    <p class="font-extrabold mt-3 leading-tight">{{ $cat['short'] }}</p>
                    <p class="text-xs text-ink-500 mt-1">{{ $cat['blurb'] }}</p>
                    <p class="text-xs font-bold mt-3" style="color: {{ $cat['color'] }}">{{ $countsByCategory[$key] ?? 0 }} lugares →</p>
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
                   class="flex gap-4 rounded-3xl bg-white border border-ink-100 shadow-soft p-5 hover:border-brand-200 hover:bg-brand-50/40 transition">
                    <span class="w-12 h-12 shrink-0 rounded-2xl bg-ink-100 grid place-items-center text-2xl">{{ $topic['emoji'] }}</span>
                    <span>
                        <span class="block font-extrabold">{{ $topic['label'] }}</span>
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
                    <a href="{{ route('guides.show', $slug) }}" class="flex items-center gap-3 rounded-2xl bg-ink-50 hover:bg-brand-50 p-3">
                        <span class="text-2xl">{{ $guide['emoji'] }}</span>
                        <span class="text-sm font-bold leading-tight">{{ $guide['title'] }}</span>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('guides.index') }}" class="inline-block mt-4 font-bold text-brand-700 hover:underline">Ver todas las guías →</a>
        </div>
        <div class="rounded-3xl bg-gradient-to-br from-brand-500 to-rose-500 text-white p-6 sm:p-8 flex flex-col">
            <p class="text-3xl">🪪</p>
            <h2 class="text-2xl font-black mt-2">Crea su perfil digital</h2>
            <p class="text-white/85 mt-1 flex-1">Foto, señas y datos de salud en un solo lugar, con un QR para la placa del collar. Si se pierde, el reporte se crea con un clic.</p>
            <a href="{{ route('profiles.create') }}" class="mt-4 rounded-2xl bg-white text-ink-900 hover:bg-ink-50 font-extrabold px-5 py-3 text-center">Crear perfil gratis</a>
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
                <p class="col-span-full text-ink-500">Todavía no hay reportes activos. ¡Ojalá siga así!</p>
            @endforelse
        </div>
    </section>

    {{-- ============ Avisos ============ --}}
    <section class="rounded-3xl bg-ink-900 text-white p-8 sm:p-10 grid md:grid-cols-[1fr_auto] gap-6 items-center">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black">Sé los ojos de tu barrio 👀</h2>
            <p class="text-white/70 mt-2 max-w-2xl">Recibe un correo cuando alguien reporte una mascota perdida o encontrada cerca de tu casa, tu trabajo o tu finca. Sin crear cuenta y te das de baja cuando quieras.</p>
        </div>
        <a href="{{ route('subscribers.create') }}" class="rounded-2xl bg-brand-500 hover:bg-brand-600 font-extrabold px-6 py-4 text-center">Activar alertas</a>
    </section>
</div>
@endsection
