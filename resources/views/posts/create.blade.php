@extends('layouts.app')

@section('title', 'Nueva publicación — '.$topic['label'].' — PetRescue')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('community.show', $slug) }}" class="text-sm font-bold text-ink-500 hover:text-ink-900">← {{ $topic['label'] }}</a>

    <div class="mt-4 mb-6">
        <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-brand-100 text-brand-700">
            {{ $topic['emoji'] }} {{ $topic['label'] }}
        </span>
        <h1 class="text-3xl sm:text-4xl font-black mt-3 tracking-tight">Nueva publicación</h1>
        <p class="text-ink-500 mt-2">
            Haz una pregunta o comparte tu experiencia. Sin crear cuenta: al publicar recibirás un enlace privado para editarla o eliminarla.
        </p>
    </div>

    {{-- HU-17: formulario de publicación --}}
    <form action="{{ route('posts.store', $slug) }}" method="POST" enctype="multipart/form-data" novalidate
          class="bg-white rounded-3xl shadow-soft border border-ink-100 p-5 sm:p-8 space-y-6">
        @csrf
        @include('posts._form', ['post' => null])

        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
            <a href="{{ route('community.show', $slug) }}" class="px-5 py-3 rounded-xl font-bold text-ink-700 hover:bg-ink-100 text-center">Cancelar</a>
            <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white bg-brand-500 hover:bg-brand-600">Publicar</button>
        </div>
    </form>
</div>
@endsection
