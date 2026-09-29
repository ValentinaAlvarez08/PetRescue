@extends('layouts.app')

@section('title', $topic['label'].' — Comunidad PetRescue')

@section('content')
<a href="{{ route('home') }}#comunidad" class="text-sm font-bold text-ink-500 hover:text-ink-900">← Comunidad</a>

<div class="mt-4 grid lg:grid-cols-[1fr_300px] gap-6">
    <div>
        <div class="rounded-3xl bg-white border border-ink-100 shadow-soft p-8">
            <span class="w-16 h-16 rounded-2xl bg-brand-50 grid place-items-center text-4xl">{{ $topic['emoji'] }}</span>
            <h1 class="text-3xl font-black mt-4">{{ $topic['label'] }}</h1>
            <p class="text-ink-500 mt-2 text-lg">{{ $topic['blurb'] }}</p>
        </div>

        <div class="mt-6 rounded-3xl border-2 border-dashed border-brand-200 bg-brand-50/60 p-8 text-center">
            <p class="text-4xl">🛠️</p>
            <p class="font-extrabold text-lg mt-2">Este espacio abre muy pronto</p>
            <p class="text-ink-500 mt-1 max-w-lg mx-auto">
                Aquí podrás publicar preguntas y experiencias, y responder a otros dueños sin necesidad de crear una cuenta.
            </p>
        </div>
    </div>

    <aside class="rounded-3xl bg-white border border-ink-100 shadow-soft p-5 h-fit">
        <p class="font-extrabold mb-3">Otros temas</p>
        <ul class="space-y-1">
            @foreach ($topics as $key => $t)
                <li>
                    <a href="{{ route('community.show', $key) }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold {{ $key === $slug ? 'bg-brand-50 text-brand-800' : 'hover:bg-ink-50' }}">
                        <span>{{ $t['emoji'] }}</span>{{ $t['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>
</div>
@endsection
