@extends('layouts.app')
@section('title','Ongekoppelde clients')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Ongekoppelde clients</h1>

<ul class="space-y-2">
@forelse($clients as $c)
    <li class="p-3 bg-white border rounded flex items-center justify-between">
        <div>
            <div class="font-medium">{{ $c->user->name ?? 'Client #'.$c->id }}</div>
            <div class="text-xs text-gray-500">
                Periode: {{ $c->profile->period_weeks ?? '—' }} w · Voorkeur: {{ $c->profile->coach_preference ?? '—' }}
            </div>
        </div>
        <form method="POST" action="{{ route('coach.clients.claim',$c) }}">
            @csrf
            <button class="px-3 py-1.5 bg-black text-white rounded text-sm">Claim</button>
        </form>
    </li>
@empty
    <li class="text-sm text-gray-500">Geen ongekoppelde clients 🎉</li>
@endforelse
</ul>
@endsection
