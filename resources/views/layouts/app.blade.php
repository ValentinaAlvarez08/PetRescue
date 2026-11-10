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
                    // Paleta: índigo (marca), pizarra azulada (texto y fondos),
                    // fucsia (mascota perdida) y cian (mascota encontrada).
                    colors: {
                        brand: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8',
                            500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81',
                        },
                        ink: {
                            50: '#f6f8fc', 100: '#edf1f7', 200: '#dbe2ee', 300: '#c3cddd', 400: '#94a3b8',
                            500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a',
                        },
                        lost: {
                            50: '#fdf4ff', 100: '#fae8ff', 200: '#f5d0fe', 300: '#f0abfc', 400: '#e879f9',
                            500: '#c026d3', 600: '#a21caf', 700: '#86198f', 800: '#701a75', 900: '#581c5c', 950: '#3b0640',
                        },
                        found: {
                            50: '#ecfeff', 100: '#cffafe', 200: '#a5f3fc', 300: '#67e8f9', 400: '#22d3ee',
                            500: '#06b6d4', 600: '#0891b2', 700: '#0e7490', 800: '#155e75', 900: '#164e63', 950: '#083344',
                        },
                    },
                    boxShadow: { soft: '0 1px 2px rgba(15,23,42,.04), 0 8px 24px -8px rgba(49,46,129,.16)' },
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

        /* ── Fondo ─────────────────────────────────────────────── */
        body::before {
            content: ""; position: fixed; inset: 0; z-index: -1; pointer-events: none;
            background:
                radial-gradient(42rem 32rem at -5% -10%, rgb(99 102 241 / .10), transparent 60%),
                radial-gradient(36rem 28rem at 105% 5%, rgb(6 182 212 / .08), transparent 60%);
        }
        ::selection { background: #c7d2fe; color: #1e1b4b; }

        /* ── Animaciones ───────────────────────────────────────── */
        @keyframes pr-fade-up { from { opacity: 0; translate: 0 14px; } to { opacity: 1; translate: none; } }
        @keyframes pr-slide-down { from { opacity: 0; translate: 0 -10px; } to { opacity: 1; translate: none; } }
        @keyframes pr-pop { 0% { opacity: 0; transform: scale(.5) rotate(-20deg); } 70% { transform: scale(1.12) rotate(6deg); } 100% { opacity: 1; transform: none; } }
        @keyframes pr-wiggle { 0%, 100% { transform: rotate(0); } 25% { transform: rotate(-14deg); } 60% { transform: rotate(10deg); } }
        @keyframes pr-float { 0%, 100% { translate: 0 0; } 50% { translate: 0 -18px; } }
        @keyframes pr-drop { 0% { opacity: 0; translate: 0 -28px; } 65% { opacity: 1; translate: 0 3px; } 100% { translate: 0 0; } }
        @keyframes pr-grow-y { from { transform: scaleY(0); } }
        @keyframes pr-grow-x { from { transform: scaleX(0); } }

        main { animation: pr-fade-up .5s cubic-bezier(.2,.7,.2,1) both; }
        .pr-logo { animation: pr-pop .6s cubic-bezier(.3,1.4,.5,1) .1s both; }
        a:hover > .pr-logo { animation: pr-wiggle .5s ease; }
        .pr-flash { animation: pr-slide-down .45s cubic-bezier(.2,.7,.2,1) both; }
        .pr-float { animation: pr-float 9s ease-in-out infinite; }
        .pr-float-slow { animation-duration: 13s; animation-delay: -4s; }
        .pr-grow-y { transform-origin: bottom; animation: pr-grow-y .9s cubic-bezier(.2,.8,.2,1) both; }
        .pr-grow-x { transform-origin: left; animation: pr-grow-x 1s cubic-bezier(.2,.8,.2,1) .15s both; }
        .pin { animation: pr-drop .55s cubic-bezier(.3,1.3,.5,1) both; }

        /* Encabezado: sombra al bajar */
        header.is-scrolled { box-shadow: 0 6px 24px -12px rgb(49 46 129 / .35); }
        header { transition: box-shadow .3s ease; }
        #nav-menu.is-open { animation: pr-slide-down .25s ease both; }

        /* Botones y enlaces: respuesta suave al tocarlos */
        a, button, input, select, textarea { transition-property: color, background-color, border-color, box-shadow, transform, translate, scale, opacity; transition-duration: .2s; transition-timing-function: ease; }
        a[class*="rounded-xl"][class*="font-bold"]:hover, a[class*="rounded-2xl"][class*="text-white"]:hover,
        button[class*="rounded-xl"]:hover { translate: 0 -2px; box-shadow: 0 10px 22px -10px rgb(49 46 129 / .45); }
        a:active, button:active { scale: .97; }
        input:focus, select:focus, textarea:focus { box-shadow: 0 0 0 4px rgb(99 102 241 / .18); }

        /* Aparición al hacer scroll (solo si hay JavaScript) */
        .pr-js [data-reveal] { opacity: 0; translate: 0 18px;
            transition: opacity .6s cubic-bezier(.2,.7,.2,1), translate .6s cubic-bezier(.2,.7,.2,1), transform .3s ease, box-shadow .3s ease, border-color .3s ease;
            transition-delay: var(--reveal-delay, 0ms), var(--reveal-delay, 0ms), 0s, 0s, 0s; }
        .pr-js [data-reveal="in"] { opacity: 1; translate: none; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important;
                transition-duration: .01ms !important; scroll-behavior: auto !important; }
            .pr-js [data-reveal] { opacity: 1; translate: none; }
        }
    </style>
    <script>
        // Las animaciones de aparición solo se activan si el navegador ejecuta JS.
        if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
            document.documentElement.classList.add('pr-js');
        }
    </script>
    @stack('head')
</head>
<body class="bg-ink-50 text-ink-900 font-sans antialiased min-h-screen flex flex-col">
    <header class="bg-white/90 backdrop-blur border-b border-ink-100 sticky top-0 z-[1000]">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-black text-xl tracking-tight">
                <span class="pr-logo w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-found-500 text-white grid place-items-center text-lg shadow-soft">🐾</span>
                Pet<span class="-ml-2 bg-gradient-to-r from-brand-600 to-found-600 bg-clip-text text-transparent">Rescue</span>
            </a>

            <button id="nav-toggle" class="lg:hidden w-10 h-10 rounded-lg hover:bg-ink-100 text-2xl" aria-label="Abrir menú">☰</button>

            @php
                $navLink = fn ($active) => 'px-3 py-2 rounded-lg font-semibold hover:bg-ink-100 '.($active ? 'text-brand-700 bg-brand-50' : 'text-ink-700');
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
                   class="px-4 py-2 rounded-xl font-bold text-white bg-lost-500 hover:bg-lost-600 text-center">Perdí mi mascota</a>
                <a href="{{ route('reports.create', 'encontrada') }}"
                   class="px-4 py-2 rounded-xl font-bold text-white bg-found-600 hover:bg-found-700 text-center">Encontré una</a>
            </nav>
        </div>
    </header>

    <main class="flex-1 @yield('main_class', 'max-w-6xl mx-auto w-full px-4 py-8')">
        @if (session('status'))
            <div class="pr-flash mb-6 bg-found-50 border border-found-200 text-found-900 px-4 py-3 rounded-xl">
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
                    <li><a class="hover:text-brand-700" href="{{ route('subscribers.create') }}">Recibir alertas de mis zonas</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('stats.index') }}">Estadísticas de reencuentros</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('profiles.create') }}">Perfil digital de mi mascota</a></li>
                </ul>
            </div>
            <div>
                <p class="font-bold text-ink-700 mb-2">Comunidad</p>
                <ul class="space-y-1">
                    <li><a class="hover:text-brand-700" href="{{ route('directory.index') }}">Directorio de servicios</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('directory.create') }}">Registrar mi negocio</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('guides.index') }}">Guías rápidas</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('community.show', 'rescatadas') }}">Mascotas rescatadas</a></li>
                    <li><a class="hover:text-brand-700" href="{{ route('community.show', 'reactivas') }}">Mascotas reactivas</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('nav-toggle').addEventListener('click', function () {
            const menu = document.getElementById('nav-menu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('is-open', !menu.classList.contains('hidden'));
        });

        // Sombra del encabezado al hacer scroll
        (function () {
            const header = document.querySelector('header');
            const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();

        // Aparición suave de secciones y tarjetas al entrar en pantalla,
        // en cascada entre hermanos (máx. 360 ms de retraso).
        (function () {
            if (!document.documentElement.classList.contains('pr-js')) return;
            const targets = document.querySelectorAll(
                'main section, main article, main figure, main .grid > *, main ul.space-y-3 > li, footer .grid > *'
            );
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.setAttribute('data-reveal', 'in');
                    observer.unobserve(entry.target);
                });
            }, { rootMargin: '0px 0px -40px 0px', threshold: 0.08 });

            targets.forEach((el) => {
                if (el.closest('.leaflet-container') || el.closest('[data-no-reveal]')) return;
                const index = el.parentElement ? Array.prototype.indexOf.call(el.parentElement.children, el) : 0;
                el.style.setProperty('--reveal-delay', Math.min(index * 60, 360) + 'ms');
                el.setAttribute('data-reveal', 'wait');
                observer.observe(el);
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
