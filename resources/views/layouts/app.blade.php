<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PetRescue — Comunidad de mascotas')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Nunito', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#fff7ed', 100: '#ffedd5', 200: '#fed7aa', 300: '#fdba74', 400: '#fb923c',
                            500: '#f97316', 600: '#ea580c', 700: '#c2410c', 800: '#9a3412', 900: '#7c2d12',
                        },
                        ink: { 50: '#f7f5f2', 100: '#efebe5', 200: '#ded7cc', 500: '#78716c', 700: '#44403c', 900: '#1c1917' },
                    },
                    boxShadow: { soft: '0 1px 2px rgba(28,25,23,.04), 0 8px 24px -8px rgba(28,25,23,.12)' },
                },
            },
        };
    </script>

    {{-- Leaflet + OpenStreetMap: mapas sin API key (HU-14 / HU-16) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .leaflet-container { font-family: inherit; }
        .pin { display:flex; align-items:center; justify-content:center; width:36px; height:36px;
               border-radius:50% 50% 50% 0; transform:rotate(-45deg); border:3px solid #fff;
               box-shadow:0 4px 10px rgba(0,0,0,.25); }
        .pin > span { transform:rotate(45deg); font-size:16px; line-height:1; }
    </style>
    @include('partials.ui-styles')
    @stack('head')
</head>
<body class="bg-ink-50 text-ink-900 font-sans antialiased min-h-screen flex flex-col">
    <div id="page-progress" aria-hidden="true"></div>
    <header id="site-header" class="bg-white/80 backdrop-blur-lg border-b border-ink-100 sticky top-0 z-[1000]">
        <div class="header-inner max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-black text-xl tracking-tight">
                <span class="logo-mark w-9 h-9 rounded-xl bg-gradient-to-br from-brand-400 to-rose-500 text-white grid place-items-center text-lg shadow-lg shadow-brand-500/30">🐾</span>
                Pet<span class="text-gradient -ml-2">Rescue</span>
            </a>

            <button id="nav-toggle" class="lg:hidden w-10 h-10 rounded-lg hover:bg-ink-100 text-2xl transition" aria-label="Abrir menú" aria-expanded="false">☰</button>

            @php
                $navLink = fn ($active) => 'nav-link px-3 py-2 rounded-lg font-semibold hover:bg-ink-100 transition-colors '.($active ? 'is-active text-brand-700 bg-brand-50' : 'text-ink-700 hover:text-ink-900');
            @endphp
            <nav id="nav-menu"
                 class="hidden lg:flex flex-col lg:flex-row lg:items-center gap-1 text-sm
                        absolute lg:static top-16 left-0 right-0 bg-white lg:bg-transparent border-b lg:border-0 border-ink-100
                        px-4 lg:px-0 py-3 lg:py-0 shadow-soft lg:shadow-none">
                <a href="{{ route('home') }}" class="{{ $navLink(request()->routeIs('home')) }}">Inicio</a>
                <a href="{{ route('reports.index') }}" class="{{ $navLink(request()->routeIs('reports.index', 'reports.show')) }}">Reportes</a>
                <a href="{{ route('directory.index') }}" class="{{ $navLink(request()->routeIs('directory.*')) }}">Directorio</a>
                <a href="{{ route('home') }}#comunidad" class="{{ $navLink(request()->routeIs('community.*')) }}">Comunidad</a>
                <a href="{{ route('guides.index') }}" class="{{ $navLink(request()->routeIs('guides.*')) }}">Guías</a>
                <a href="{{ route('subscribers.create') }}" class="{{ $navLink(request()->routeIs('subscribers.*')) }}">Alertas</a>
                <a href="{{ route('stats.index') }}" class="{{ $navLink(request()->routeIs('stats.*')) }}">Estadísticas</a>
                <span class="hidden lg:block w-px h-6 bg-ink-200 mx-2"></span>
                <a href="{{ route('reports.create', 'perdida') }}"
                   class="px-4 py-2 rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-600 hover:-translate-y-0.5 shadow-md shadow-rose-500/25 transition text-center">Perdí mi mascota</a>
                <a href="{{ route('reports.create', 'encontrada') }}"
                   class="px-4 py-2 rounded-xl font-bold text-white bg-emerald-600 hover:bg-emerald-700 hover:-translate-y-0.5 shadow-md shadow-emerald-600/25 transition text-center">Encontré una</a>
            </nav>
        </div>
        <div id="scroll-progress" aria-hidden="true"></div>
    </header>

    <main class="flex-1 @yield('main_class', 'max-w-6xl mx-auto w-full px-4 py-8')">
        @if (session('status'))
            <div class="@hasSection('main_class') max-w-6xl mx-auto px-4 pt-6 @endif">
                <div class="toast relative mb-6 flex items-start gap-3 bg-gradient-to-r from-emerald-50 to-white border border-emerald-200 text-emerald-900 pl-4 pr-12 py-4 rounded-2xl shadow-soft overflow-hidden" role="status">
                    <span class="w-9 h-9 shrink-0 rounded-full bg-emerald-500 text-white grid place-items-center font-black shadow-md shadow-emerald-500/30">✓</span>
                    <p class="font-semibold pt-1.5">{{ session('status') }}</p>
                    <button type="button" data-toast-close class="absolute top-3 right-3 w-8 h-8 rounded-lg hover:bg-emerald-100 text-emerald-700 font-black" aria-label="Cerrar aviso">✕</button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="relative mt-16 bg-ink-900 text-white/70">
        <svg class="absolute -top-px left-0 w-full h-10 -translate-y-full text-ink-900" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true">
            <path fill="currentColor" d="M0,40 C240,0 480,60 720,32 C960,4 1200,52 1440,24 L1440,60 L0,60 Z"/>
        </svg>
        <div class="max-w-6xl mx-auto px-4 pt-12 pb-8 grid gap-8 sm:grid-cols-3 text-sm">
            <div>
                <p class="font-black text-white text-xl flex items-center gap-2"><span class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-400 to-rose-500 grid place-items-center">🐾</span> PetRescue</p>
                <p class="mt-3 leading-relaxed">La comunidad de quienes cuidan mascotas en Pasto. Sin cuentas, sin complicaciones.</p>
            </div>
            <div>
                <p class="font-extrabold text-white mb-3 uppercase tracking-wider text-xs">Reportes</p>
                <ul class="space-y-1">
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('reports.create', 'perdida') }}">Reportar mascota perdida</a></li>
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('reports.create', 'encontrada') }}">Reportar mascota encontrada</a></li>
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('subscribers.create') }}">Recibir alertas de mis zonas</a></li>
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('stats.index') }}">Estadísticas de reencuentros</a></li>
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('profiles.create') }}">Perfil digital de mi mascota</a></li>
                </ul>
            </div>
            <div>
                <p class="font-extrabold text-white mb-3 uppercase tracking-wider text-xs">Comunidad</p>
                <ul class="space-y-1">
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('directory.index') }}">Directorio de servicios</a></li>
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('directory.create') }}">Registrar mi negocio</a></li>
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('guides.index') }}">Guías rápidas</a></li>
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('community.show', 'rescatadas') }}">Mascotas rescatadas</a></li>
                    <li><a class="inline-block hover:text-brand-300 hover:translate-x-1 transition" href="{{ route('community.show', 'reactivas') }}">Mascotas reactivas</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="max-w-6xl mx-auto px-4 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-white/50">
                <p>© {{ date('Y') }} PetRescue · Pasto, Colombia</p>
                <p>Hecho con <span class="text-rose-400">♥</span> para que todos vuelvan a casa</p>
            </div>
        </div>
    </footer>

    <button id="to-top" type="button" aria-label="Volver arriba"
            class="fixed bottom-6 right-6 z-[1500] w-12 h-12 rounded-2xl bg-ink-900 text-white text-xl font-black shadow-xl hover:bg-brand-600 hover:-translate-y-1 transition">↑</button>

    @include('partials.ui-scripts')
    @stack('scripts')
</body>
</html>
