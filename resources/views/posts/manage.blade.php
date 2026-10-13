@extends('layouts.app')

@section('title', 'Gestionar mi publicación — PetRescue')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- HU-17: vista privada (solo con el enlace que contiene el token) --}}
    <div class="rounded-3xl bg-ink-900 text-white p-6 sm:p-8">
        <p class="text-xs font-extrabold uppercase tracking-wider text-brand-300">🔒 Enlace privado</p>
        <h1 class="text-2xl sm:text-3xl font-black mt-2">Gestiona tu publicación</h1>
        <p class="text-white/75 mt-2">
            Guarda este enlace (en favoritos o enviándotelo por WhatsApp). Es la única forma de editar o eliminar tu publicación.
            No lo compartas.
        </p>
        <div class="mt-4 flex flex-col sm:flex-row gap-2">
            <input id="manage-link" type="text" readonly value="{{ route('posts.manage', $post->management_token) }}"
                   class="flex-1 min-w-0 rounded-xl bg-white/10 border border-white/20 px-4 py-3 text-sm">
            <button type="button" id="copy-link" class="rounded-xl bg-brand-500 hover:bg-brand-600 font-bold px-4 py-3 text-sm">Copiar enlace</button>
        </div>
        <a href="{{ route('posts.show', [$post->topic, $post]) }}" class="inline-block mt-4 text-sm font-bold text-brand-300 hover:text-brand-200">
            Ver la publicación como la ve la comunidad →
        </a>
    </div>

    <form action="{{ route('posts.update', $post->management_token) }}" method="POST" enctype="multipart/form-data" novalidate
          class="mt-6 bg-white rounded-3xl shadow-soft border border-ink-100 p-5 sm:p-8 space-y-6">
        @csrf
        @method('PATCH')
        <div>
            <p class="text-sm text-ink-500">{{ $topic['emoji'] }} {{ $topic['label'] }}</p>
            <h2 class="text-xl font-extrabold">Editar publicación</h2>
        </div>
        @include('posts._form', ['post' => $post])
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white bg-brand-500 hover:bg-brand-600">Guardar cambios</button>
        </div>
    </form>

    <form action="{{ route('posts.destroy', $post->management_token) }}" method="POST"
          onsubmit="return confirm('¿Seguro que quieres eliminar tu publicación? También se borrarán sus comentarios y no se puede deshacer.');"
          class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        @csrf
        @method('DELETE')
        <div>
            <p class="font-extrabold text-rose-800">Eliminar publicación</p>
            <p class="text-sm text-rose-700">Se borrará junto con sus comentarios. Esta acción no se puede deshacer.</p>
        </div>
        <button type="submit" class="shrink-0 px-5 py-3 rounded-xl font-bold text-white bg-rose-600 hover:bg-rose-700">Eliminar</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('copy-link').addEventListener('click', function () {
    const input = document.getElementById('manage-link');
    input.select();
    navigator.clipboard?.writeText(input.value);
    this.textContent = '¡Copiado!';
});
</script>
@endpush
