{{--
    Sprint 4 — HU-09: compartir el reporte en redes sociales.
    Todos los enlaces usan $report->public_url, nunca el enlace privado de gestión (CID 4).
--}}
@php $links = $report->shareLinks(); @endphp
<div class="bg-white rounded-3xl shadow-soft border border-ink-100 p-6" data-share-box>
    <button type="button" data-share-toggle aria-expanded="false" aria-controls="share-options"
            class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-extrabold py-3">
        📣 Compartir
    </button>
    <p class="text-xs text-ink-500 mt-2 text-center">Entre más personas lo vean, más fácil es el reencuentro.</p>

    <div id="share-options" data-share-options class="hidden mt-4 grid grid-cols-2 gap-2">
        <a href="{{ $links['whatsapp'] }}" target="_blank" rel="noopener" data-network="whatsapp"
           class="flex items-center justify-center gap-2 rounded-xl bg-[#25D366] hover:brightness-95 text-white font-bold py-3 text-sm">💬 WhatsApp</a>
        <a href="{{ $links['facebook'] }}" target="_blank" rel="noopener" data-network="facebook"
           class="flex items-center justify-center gap-2 rounded-xl bg-[#1877F2] hover:brightness-95 text-white font-bold py-3 text-sm">👍 Facebook</a>
        <a href="{{ $links['x'] }}" target="_blank" rel="noopener" data-network="x"
           class="flex items-center justify-center gap-2 rounded-xl bg-ink-900 hover:bg-ink-700 text-white font-bold py-3 text-sm">𝕏 X</a>
        <button type="button" data-copy="{{ $report->public_url }}" data-copied-text="✅ ¡Enlace copiado!"
                class="flex items-center justify-center gap-2 rounded-xl border border-ink-200 hover:bg-ink-50 font-bold py-3 text-sm">🔗 Copiar enlace</button>
        <input readonly value="{{ $report->public_url }}" aria-label="Enlace público del reporte" onclick="this.select()"
               class="col-span-2 rounded-xl border border-ink-200 bg-ink-50 px-3 py-2 text-xs text-ink-700">
    </div>
</div>
