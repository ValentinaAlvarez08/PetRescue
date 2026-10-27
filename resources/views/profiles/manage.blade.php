@extends('layouts.app')

{{-- Sprint 4 — HU-10 (CID 3 y 4): enlace privado del perfil: QR, reportar pérdida, editar, eliminar --}}
@php $lostReport = $profile->activeLostReport; @endphp

@section('title', 'Perfil de '.$profile->name.' — PetRescue')

@push('head')
    <meta name="robots" content="noindex, nofollow">
    <style>
        @media print {
            body * { visibility: hidden; }
            #tag, #tag * { visibility: visible; }
            #tag { position: absolute; left: 0; top: 0; }
        }
    </style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="rounded-3xl bg-ink-900 text-white p-6 sm:p-8">
        <p class="text-xs font-extrabold uppercase tracking-wider text-brand-300">🔒 Enlace privado</p>
        <h1 class="text-2xl sm:text-3xl font-black mt-2">Perfil digital de {{ $profile->name }}</h1>
        <p class="text-white/75 mt-2">Guarda este enlace: es la única forma de editar el perfil o reportar que se perdió. No lo compartas.
            Para mostrar el perfil usa el enlace público o el QR.</p>
        <div class="mt-4 flex flex-col sm:flex-row gap-2">
            <input readonly value="{{ route('profiles.manage', $profile->management_token) }}" onclick="this.select()"
                   class="flex-1 min-w-0 rounded-xl bg-white/10 border border-white/20 px-4 py-3 text-sm">
            <button type="button" data-copy="{{ route('profiles.manage', $profile->management_token) }}"
                    class="rounded-xl bg-brand-500 hover:bg-brand-600 font-bold px-4 py-3 text-sm">Copiar enlace privado</button>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- CID 3: placa con QR para el collar --}}
        <div class="bg-white rounded-3xl shadow-soft border border-ink-100 p-6">
            <h2 class="font-extrabold text-lg">🏷️ Placa con código QR</h2>
            <p class="text-sm text-ink-500 mt-1">Imprímela y ponla en el collar, o llévala a hacer en metal. Quien la escanee verá el perfil público.</p>
            <div id="tag" class="mt-4 mx-auto w-56 rounded-2xl border-2 border-ink-900 p-4 text-center">
                <p class="font-black text-lg leading-tight">{{ $profile->name }}</p>
                <div id="qr" class="mt-2 flex justify-center" data-url="{{ $profile->public_url }}"></div>
                <p class="text-xs font-bold mt-2">Si me encuentras, escanéame 🐾</p>
                <p class="text-xs">{{ $profile->contact_phone }}</p>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <button type="button" onclick="window.print()" class="rounded-xl bg-ink-900 hover:bg-ink-700 text-white font-bold py-3 text-sm">🖨️ Imprimir placa</button>
                <a href="{{ $profile->public_url }}" target="_blank" rel="noopener" class="rounded-xl border border-ink-200 hover:bg-ink-50 font-bold py-3 text-sm text-center">Ver perfil público</a>
            </div>
            <input readonly value="{{ $profile->public_url }}" aria-label="Enlace público del perfil" onclick="this.select()"
                   class="mt-2 w-full rounded-xl border border-ink-200 bg-ink-50 px-3 py-2 text-xs text-ink-700">
        </div>

        {{-- CID 4: reportar la pérdida con los datos del perfil --}}
        <div class="rounded-3xl border-2 {{ $lostReport ? 'border-rose-300 bg-rose-50' : 'border-ink-100 bg-white' }} shadow-soft p-6">
            @if ($lostReport)
                <h2 class="font-extrabold text-lg text-rose-800">🚨 {{ $profile->name }} tiene un reporte de pérdida activo</h2>
                <p class="text-sm text-rose-900/80 mt-1">El perfil público muestra el aviso «¡Está perdida!» con el enlace al reporte.</p>
                <a href="{{ route('reports.show', $lostReport) }}" class="mt-4 block text-center rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-extrabold py-3">Ver el reporte</a>
                <p class="text-xs text-rose-900/70 mt-2">Para marcarlo como reunido usa el enlace privado que recibiste al publicar el reporte.</p>
            @else
                <h2 class="font-extrabold text-lg">😿 ¿Se perdió {{ $profile->name }}?</h2>
                <p class="text-sm text-ink-500 mt-1">Crea el reporte con la foto, las señas y tu contacto ya llenos. Solo tienes que marcar dónde se perdió.</p>
                <a href="{{ route('reports.create', ['type' => 'perdida', 'perfil' => $profile->management_token]) }}" data-role="report-lost"
                   class="mt-4 block text-center rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-extrabold py-3">Reportar que se perdió</a>
            @endif

            @if ($profile->reports->isNotEmpty())
                <p class="text-sm font-bold mt-5">Reportes anteriores</p>
                <ul class="mt-1 text-sm space-y-1">
                    @foreach ($profile->reports as $report)
                        <li><a href="{{ route('reports.show', $report) }}" class="hover:underline">{{ $report->created_at->format('d/m/Y') }} · {{ ['activo' => 'Activo', 'reunido' => 'Reunido', 'cerrado' => 'Cerrado'][$report->status] ?? $report->status }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <form action="{{ route('profiles.update', $profile->management_token) }}" method="POST" enctype="multipart/form-data" novalidate
          class="bg-white rounded-3xl shadow-soft border border-ink-100 p-5 sm:p-8 space-y-8">
        @csrf
        @method('PATCH')
        <h2 class="text-xl font-black">Editar el perfil</h2>
        @include('profiles._form', ['profile' => $profile])
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 rounded-xl font-extrabold text-white bg-brand-500 hover:bg-brand-600">Guardar cambios</button>
        </div>
    </form>

    <form action="{{ route('profiles.destroy', $profile->management_token) }}" method="POST"
          onsubmit="return confirm('¿Eliminar este perfil? El QR de la placa dejará de funcionar.');"
          class="rounded-3xl border border-rose-200 bg-rose-50 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        @csrf
        @method('DELETE')
        <div>
            <p class="font-extrabold text-rose-800">Eliminar perfil</p>
            <p class="text-sm text-rose-700">El enlace público y el QR dejarán de funcionar. Los reportes que hayas publicado se conservan.</p>
        </div>
        <button type="submit" class="shrink-0 px-5 py-3 rounded-xl font-bold text-white bg-rose-600 hover:bg-rose-700">Eliminar</button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    const qr = document.getElementById('qr');
    if (window.QRCode) new QRCode(qr, { text: qr.dataset.url, width: 160, height: 160, correctLevel: QRCode.CorrectLevel.M });

    document.querySelectorAll('[data-copy]').forEach(b => b.addEventListener('click', () => {
        navigator.clipboard?.writeText(b.dataset.copy);
        b.textContent = '¡Copiado!';
    }));
})();
</script>
@endpush
