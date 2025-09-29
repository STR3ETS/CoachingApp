@extends('layouts.app')
@section('title','Threads')

@section('content')
@if($role === 'coach')
    <h1 class="text-2xl font-bold mb-2">Chat met je klanten</h1>
    <p class="text-sm text-black opacity-80 font-medium mb-10">Chat snel en gemakkelijk met jouw klanten<br>en begeleid ze door hun traject!</p>
    @elseif($role === 'client')
    <h1 class="text-2xl font-bold mb-2">Chat met je coach</h1>
    <p class="text-sm text-black opacity-80 font-medium mb-10">Chat snel en gemakkelijk met jouw coach.<br>Beschrijf wat je nodig hebt of geef blessures door.</p>
@endif

@if($role === 'client')
    @can('create', App\Models\Thread::class)
        <h2 class="text-lg font-bold mb-2">Snelkoppelingen</h2>
        <div class="grid gap-3 grid-cols-1 md:grid-cols-4 mb-6">
            <a href="{{ route('client.threads.create') }}"
            class="p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded">
                <div class="font-semibold text-white">Nieuwe chat met coach</div>
                <p class="text-sm text-white">Start een nieuw gesprek</p>
            </a>
        </div>
    @endcan
@endif

<ul class="mt-7 flex flex-col gap-2">
    <h2 class="text-lg font-bold">Gesprekken</h2>
    @forelse($threads as $t)
        @php
            $href = $role === 'client'
            ? route('client.threads.show', $t)
            : route('coach.threads.show',  $t);

            $clientName = data_get($t, 'client.user.name')
            ?? data_get($t, 'client.name')
            ?? data_get($t, 'client_name')
            ?? 'Onbekende klant';
        @endphp
        <li>
            <a href="{{ $href }}"
            class="p-6 bg-white rounded-2xl border hover:border-[#c8ab7a] flex items-center justify-between focus:outline-none transition duration-300">
                <div>
                    @if($role === 'coach')
                        <span class="text-xs text-gray-500 inline-flex items-center px-2 py-0.5 rounded border mb-2">
                            {{ $clientName }}
                        </span>
                    @endif

                    <div class="font-semibold text-sm mb-2">
                        {{ $t->subject ?? 'Gesprek' }}
                    </div>

                    <div class="flex text-xs items-center gap-2">
                        <span>{{ $t->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                </div>
                @if($role === 'coach')
                    <div class="w-4 h-4 bg-green-500 animate-pulse rounded-full"></div>
                @endif
            </a>
        </li>
    @empty
        <li class="p-3 bg-white rounded border text-sm text-gray-500">Geen threads gevonden.</li>
    @endforelse
</ul>
@endsection
