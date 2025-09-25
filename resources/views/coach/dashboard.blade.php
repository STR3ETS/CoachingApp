@extends('layouts.app')
@section('title','Coach dashboard')

@section('content')
<h1 class="text-2xl font-bold mb-2">Goededag {{ auth()->user()->name }}! 👋</h1>
<p class="text-sm text-black opacity-80 font-medium mb-10">Welkom op jouw persoonlijke training omgeving.<br>Beheer jouw klanten, chat met je klanten en maak trainingsschemas aan.</p>

{{-- Snelkoppelingen --}}
<h2 class="text-lg font-bold mb-2">Snelkoppelingen</h2>
<div class="grid gap-3 grid-cols-1 sm:grid-cols-3 mb-6">
    <a href="{{ route('coach.threads.index') }}"
       class="p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded">
        <div class="font-semibold text-white">Alle threads</div>
        <p class="text-sm text-white">Bekijk en beantwoord gesprekken</p>
    </a>

    <a href="{{ route('coach.clients.unassigned') }}"
       class="p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded">
        <div class="font-semibold text-white">Ongeclaimde clients</div>
        <p class="text-sm text-white">Claim nieuwe aanmeldingen</p>
    </a>

    <a href="{{ route('coach.plans.index') }}"
       class="p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded">
        <div class="font-semibold text-white">Trainingsplannen</div>
        <p class="text-sm text-white">Overzicht van alle plannen</p>
    </a>
</div>

<h2 class="text-lg font-bold mb-2">Informatie</h2>
@php
    use App\Models\Client;
    use App\Models\Payment;
    use Carbon\Carbon;

    // Aantal klanten
    $totalClients = Client::count();

    // Totale omzet uit payments.amount
    // Als 'amount' in centen staat: deel door 100.
    $rawRevenue   = Payment::sum('amount');
    // $rawRevenue = Payment::whereIn('status', ['paid','succeeded'])->sum('amount'); // <- optioneel filter

    // Zet dit op true als 'amount' centen zijn:
    $amountIsCents = false;

    $totalRevenue = $amountIsCents ? ($rawRevenue / 100) : $rawRevenue;
    $currency     = '€';
    $asOf         = Carbon::now()->format('d-m-Y');
@endphp
<section class="grid gap-4 grid-cols-2 mb-4">
    {{-- Totaal klanten --}}
    <div class="p-6 bg-white rounded-3xl border">
        <div class="text-sm text-black font-semibold opacity-50 mb-1">Totaal klanten</div>
        <div class="text-3xl font-bold text-[#c8ab7a] mb-2">
            {{ number_format($totalClients, 0, ',', '.') }}
        </div>
        <div class="text-[12px] text-gray-500">
            Status t/m {{ $asOf }}
        </div>
    </div>

    {{-- Totale omzet uit payments.amount --}}
    <div class="p-6 bg-white rounded-3xl border">
        <div class="text-sm text-black font-semibold opacity-50 mb-1">Totale omzet</div>

        <div class="flex items-end justify-between gap-4">
            {{-- Bedrag links --}}
            <div>
                <div class="text-3xl font-bold text-[#c8ab7a] mb-1">
                    {{ $currency }} {{ number_format($totalRevenue, 2, ',', '.') }}
                </div>
                <div class="text-[12px] text-gray-500">
                    Gebaseerd op alle betalingen (t/m {{ $asOf }})
                </div>
            </div>

            {{-- Mini-graph rechts (hardcoded stijgende sparkline) --}}
            <div class="shrink-0">
                <svg viewBox="0 0 120 44" width="120" height="44" aria-hidden="true">
                    <!-- lichte achtergrond -->
                    <rect x="0" y="0" width="120" height="44" rx="8" class="fill-white"></rect>

                    <!-- zachte area-fill onder de lijn -->
                    <defs>
                    <linearGradient id="revFill" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#00c951" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#00c951" stop-opacity="0"/>
                    </linearGradient>
                    </defs>

                    <!-- polygon voor fill (sluit naar onderrand) -->
                    <polygon fill="url(#revFill)" points="
                    0,36  10,34  20,32  30,30  40,28  50,25  60,22
                    70,19  80,16  90,13  100,10  110,7  120,5
                    120,44 0,44
                    "></polygon>

                    <!-- stijgende lijn -->
                    <polyline
                    fill="none"
                    stroke="#00c95150"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    points="0,36 10,34 20,32 30,30 40,28 50,25 60,22 70,19 80,16 90,13 100,10 110,7 120,5"
                    ></polyline>
                </svg>
            </div>
        </div>
    </div>
</section>
<div class="grid md:grid-cols-1 gap-4">
    {{-- Threads overzicht --}}
    <section class="p-6 bg-white rounded-3xl border">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Gesprekken</h2>
            <a href="{{ route('coach.threads.index') }}" class="text-xs opacity-50 hover:opacity-100 transition duration-300 font-semibold">
                Alle gesprekken bekijken
            </a>
        </div>

        @if(isset($threads) && $threads->count())
            <ul class="flex flex-col gap-2">
                @foreach($threads as $t)
                    <li class="bg-gray-50 overflow-hidden rounded-2xl border hover:border-[#c8ab7a] flex items-center justify-between focus:outline-none transition duration-300">
                        <a href="{{ route('coach.threads.show', $t) }}"
                           class="w-full flex items-center justify-between p-3 rounded">
                            <div>
                                <span class="text-xs text-gray-500 inline-flex items-center px-2 py-0.5 rounded border mb-2">
                                    {{ $t->client->user->name ?? '—' }}
                                </span>
                                <div class="font-semibold text-sm mb-2">
                                    {{ $t->subject ?? 'Gesprek' }}
                                </div>
                                <span class="flex text-xs items-center gap-2">
                                    <span>{{ $t->created_at->format('d-m-Y H:i') }}</span>
                                </span>
                            </div>
                            <div class="w-4 h-4 bg-green-500 animate-pulse rounded-full"></div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Nog geen gesprekken.</p>
        @endif
    </section>

    {{-- Mijn clients --}}
    <aside class="p-6 bg-white rounded-3xl border">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Jouw klanten</h2>
            <a href="{{ route('coach.clients.unassigned') }}" class="text-xs opacity-50 hover:opacity-100 transition duration-300 font-semibold">
                Ongeclaimde klanten bekijken
            </a>
        </div>

        @if(isset($clients) && $clients->count())
            <ul class="flex flex-col gap-2 text-sm">
                @foreach($clients as $c)
                    <li>
                        <a href="{{ route('coach.clients.show', $c) }}" class="bg-gray-50 p-4 overflow-hidden rounded-2xl border hover:border-[#c8ab7a] flex items-center justify-between focus:outline-none transition duration-300">
                            <div class="flex flex-col gap-2">
                                <div class="text-sm text-black font-semibold">
                                    {{ $c->user->name ?? 'Client #'.$c->id }}
                                </div>
                                <div class="flex items-center gap-2 animate-pulse">
                                    <p class="text-green-500 font-semibold text-[12px]">Actief</p>
                                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Nog geen gekoppelde clients.</p>
        @endif
    </aside>
</div>
@endsection
