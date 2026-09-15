<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PetRescue')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <header class="bg-gray-800 text-white sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('reports.index') }}" class="text-xl font-bold">🐾 PetRescue</a>

            <button id="nav-toggle" class="sm:hidden text-white text-2xl leading-none" aria-label="Abrir menú">☰</button>

            <nav id="nav-menu" class="hidden sm:flex flex-col sm:flex-row gap-3 sm:items-center text-sm
                        absolute sm:static top-full left-0 right-0 bg-gray-800 sm:bg-transparent
                        px-4 sm:px-0 py-4 sm:py-0">
                <a href="{{ route('reports.index') }}"
                   class="hover:underline {{ request()->routeIs('reports.index') ? 'font-semibold underline' : '' }}">Inicio</a>
                <a href="{{ route('subscribers.create') }}"
                   class="hover:underline {{ request()->routeIs('subscribers.create') ? 'font-semibold underline' : '' }}">🔔 Avísame de reportes cercanos</a>
                <a href="{{ route('reports.create', 'perdida') }}"
                   class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded text-center">Perdí una mascota</a>
                <a href="{{ route('reports.create', 'encontrada') }}"
                   class="bg-green-600 hover:bg-green-700 px-3 py-2 rounded text-center">Encontré una mascota</a>
            </nav>
        </div>
    </header>

    <script>
        document.getElementById('nav-toggle').addEventListener('click', function () {
            document.getElementById('nav-menu').classList.toggle('hidden');
        });
    </script>

    <main class="max-w-4xl mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
