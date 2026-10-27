{{--
    Sprint 4 — HU-20: botón "Denunciar" que despliega el formulario con el motivo.
    Funciona sin JavaScript (<details>). Si hay varios en la página (publicación y
    comentarios), "target" indica cuál se abre con los errores de validación.
--}}
@props([
    'action',
    'target',
    'label' => 'Denunciar',
])

@php
    $bag = $errors->getBag('flag');
    $mine = old('flag_target') === $target && $bag->any();
@endphp

<details {{ $attributes->merge(['class' => 'group text-sm']) }} @if ($mine) open @endif data-flag="{{ $target }}">
    <summary class="list-none [&::-webkit-details-marker]:hidden cursor-pointer inline-flex items-center gap-1 font-semibold text-ink-500 hover:text-rose-600">
        🚩 {{ $label }}
    </summary>

    <form method="POST" action="{{ $action }}" class="mt-2 max-w-sm rounded-2xl border border-ink-200 bg-white p-4 shadow-soft space-y-3 text-ink-900">
        @csrf
        <input type="hidden" name="flag_target" value="{{ $target }}">
        <p class="font-extrabold">¿Por qué es inapropiado?</p>

        <div class="space-y-1.5">
            @foreach (config('community.flags.reasons') as $key => $reasonLabel)
                <label class="flex items-center gap-2">
                    <input type="radio" name="reason" value="{{ $key }}" class="accent-rose-600" @checked($mine && old('reason') === $key)>
                    {{ $reasonLabel }}
                </label>
            @endforeach
        </div>

        <textarea name="details" rows="2" maxlength="300" placeholder="Detalles (obligatorio si eliges «Otro motivo»)"
                  class="w-full rounded-xl border border-ink-200 bg-ink-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-200">{{ $mine ? old('details') : '' }}</textarea>

        @if ($mine)
            <ul class="text-rose-600 space-y-0.5">
                @foreach ($bag->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <p class="text-xs text-ink-500">Si varias personas lo denuncian, se oculta mientras lo revisamos.</p>
        <button type="submit" class="w-full rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold py-2">Enviar denuncia</button>
    </form>
</details>
