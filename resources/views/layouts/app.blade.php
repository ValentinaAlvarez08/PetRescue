<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PetRescue')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <header class="bg-gray-800 text-white">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('reports.index') }}" class="text-xl font-bold">🐾 PetRescue</a>
            <nav class="space-x-3 text-sm">
                <a href="{{ route('reports.create', 'perdida') }}" class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded">Perdí una mascota</a>
                <a href="{{ route('reports.create', 'encontrada') }}" class="bg-green-600 hover:bg-green-700 px-3 py-2 rounded">Encontré una mascota</a>
            </nav>
        </div>
    </header>

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
