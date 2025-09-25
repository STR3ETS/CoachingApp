@extends('layouts.app')
@section('title','Plannen')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Trainingplannen</h1>

<div class="grid md:grid-cols-2 gap-6">
    {{-- Overzicht van mijn plannen --}}
    <section class="p-4 bg-white rounded border">
        <h2 class="font-semibold mb-3">Mijn plannen</h2>
        <ul class="space-y-2 text-sm">
            @forelse($plans as $p)
                <li class="p-2 border rounded flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $p->title }} ({{ $p->weeks }} w)</div>
                        <div class="text-xs text-gray-500">Plan #{{ $p->id }} · Client #{{ $p->client_id }}</div>
                    </div>
                    <a href="{{ route('coach.plans.show',$p) }}" class="underline">Open</a>
                </li>
            @empty
                <li class="text-gray-500">Nog geen plannen.</li>
            @endforelse
        </ul>
    </section>

    {{-- Concept genereren vanuit gekoppelde clients --}}
    <section class="p-4 bg-white rounded border">
        <h2 class="font-semibold mb-3">Genereer concept</h2>
        <ul class="space-y-2 text-sm">
            @forelse($clients as $c)
                <li class="p-2 border rounded flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $c->user->name ?? 'Client #'.$c->id }}</div>
                        <div class="text-xs text-gray-500">Periode: {{ $c->profile->period_weeks ?? '—' }} w</div>
                    </div>
                    <form method="POST" action="{{ route('coach.plans.generate',$c) }}">
                        @csrf
                        <button class="px-3 py-1.5 bg-black text-white rounded">Genereer</button>
                    </form>
                </li>
            @empty
                <li class="text-gray-500">Nog geen gekoppelde clients.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
