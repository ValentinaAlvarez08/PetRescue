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
    @stack('head')
</head>
<body class="bg-ink-50 text-ink-900 font-sans antialiased min-h-screen flex flex-col">
    <header class="bg-white/90 backdrop-blur border-b border-ink-100 sticky top-0 z-[1000]">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-black text-xl tracking-tight">
                <span class="w-9 h-9 rounded-xl bg-brand-500 text-white grid place-items-center text-lg">🐾</span>
                Pet<span class="text-brand-600 -ml-2">Rescue</span>
            </a>

            <button id="nav-toggle" class="md:hidden w-10 h-10 rounded-lg hover:bg-ink-100 text-2xl" aria-label="Abrir menú">☰</button>

            @php
                $navLink = fn ($active) => 'px-3 py-2 rounded-lg font-semibold hover:bg-ink-100 '.($active ? 'text-brand-700 bg-brand-50' : 'text-ink-700');
            @endphp
            <nav id="nav-menu"
                 class="hidden md:flex flex-col md:flex-row md:items-center gap-1 text-sm
                        absolute md:static top-16 left-0 right-0 bg-white md:bg-transparent border-b md:border-0 border-ink-100
                        px-4 md:px-0 py-3 md:py-0 shadow-soft md:shadow-none">
                <a href="{{ route('home') }}" class="{{ $navLink(request()->routeIs('home')) }}">Inicio</a>
                <a href="{{ route('reports.index') }}" class="{{ $navLink(request()->routeIs('reports.index', 'reports.show')) }}">Reportes</a>
                <a href="{{ route('directory.index') }}" class="{{ $navLink(request()->routeIs('directory.*')) }}">Directorio</a>
                <a href="{{ route('home') }}#comunidad" class="{{ $navLink(request()->routeIs('community.*')) }}">Comunidad</a>
                <a href="{{ route('subscribers.create') }}" class="{{ $navLink(request()->routeIs('subscribers.*')) }}">Avisos</a>
                <span class="hidden md:block w-px h-6 bg-ink-200 mx-2"></span>
                <a href="{{ route('reports.create', 'perdida') }}"
                   class="px-4 py-2 rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-600 text-center">Perdí mi mascota</a>
                <a href="{{ route('reports.create', 'encontrada') }}"
                   class="px-4 py-2 rounded-xl font-bold text-white bg-emerald-600 hover:bg-emerald-700 text-center">Encontré una</a>
            </nav>
        </div>
    </header>

    <main class="flex-1 @yield('main_class', 'max-w-6xl mx-auto w-full px-4 py-8')">
        @if (session('status'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 rounded-xl">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-ink-100 bg-white">
        <div class="max-w-6xl mx-auto px-4 py-8 grid gap-6 sm:grid-cols-3 text-sm text-ink-500">
            <div>
                <p class="font-black text-ink-900 text-base">🐾 PetRescue</p>
                <p class="mt-1">La comunidad de quienes cuidan mascotas en Pasto. Sin cuentas, sin complicaciones.</p>
            </div>
            <div>
                <p class="font-bold text-ink-700 mb-2">Reportes</p>
                <ul class="space-y-1">
                    <li><a class="hover:text-brand-700" href="{{ route('reports.create', 'perdida') }}">Reportar mascota perdida</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('reports.create', 'encontrada') }}">Reportar mascota encontrada</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('subscribers.create') }}">Recibir avisos de mi zona</a></li>
                </ul>
            </div>
            <div>
                <p class="font-bold text-ink-700 mb-2">Comunidad</p>
                <ul class="space-y-1">
                    <li><a class="hover:text-brand-700" href="{{ route('directory.index') }}">Directorio de servicios</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('community.show', 'rescatadas') }}">Mascotas rescatadas</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('community.show', 'reactivas') }}">Mascotas reactivas</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('nav-toggle').addEventListener('click', function () {
            document.getElementById('nav-menu').classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
