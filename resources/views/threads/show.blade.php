@extends('layouts.app')
@section('title','Gesprek #'.$thread->id)

@section('content')
<div class="flex gap-8">
    @if($role === 'client')
        @php
            $client  = optional(auth()->user())->client;
            $coachId = optional($client)->coach_id;

            // VERVANG deze IDs door de echte IDs uit je database
            $ELINE_ID = 1;
            $NICKY_ID = 2;
            $ROY_ID   = 3;
        @endphp
        @if($coachId === $NICKY_ID)
            <div class="min-w-[250px] max-w-[200px] h-fit p-6 bg-white rounded-3xl mt-[6.5rem]">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-black bg-cover bg-top relative bg-[url(https://cdn6.site-media.eu/images/640%2C1160x772%2B130%2B112/18694492/coachNicky-MjbAPBl6Pr1a23o9d6zbqA.webp)]">
                        <div class="w-3 h-3 bg-green-500 animate-ping rounded-full absolute left-0 top-0"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full absolute left-0 top-0"></div>
                    </div>
                    <div>
                        <h2 class="text-xs text-black font-semibold">Nicky Verhoeven</h2>
                        <h3 class="text-xs text-black/50 font-medium">Jouw coach</h3>
                    </div>
                </div>
            </div>
        @elseif($coachId === $ELINE_ID)
            <div class="min-w-[250px] max-w-[200px] h-fit p-6 bg-white rounded-3xl mt-[6.5rem]">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-black bg-cover bg-top relative bg-[url(https://cdn6.site-media.eu/images/576%2C1160x772%2B150%2B121/18694504/coachEline-DVsTZnUZ-eQ_EWm1zNyfww.webp)]">
                        <div class="w-3 h-3 bg-green-500 animate-ping rounded-full absolute left-0 top-0"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full absolute left-0 top-0"></div>
                    </div>
                    <div>
                        <h2 class="text-xs text-black font-semibold">Eline Verhoeven</h2>
                        <h3 class="text-xs text-black/50 font-medium">Jouw coach</h3>
                    </div>
                </div>
            </div>
        @elseif($coachId === $ROY_ID)
            <div class="min-w-[250px] max-w-[200px] h-fit p-6 bg-white rounded-3xl mt-[6.5rem]">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-black bg-cover bg-top relative bg-[url(https://cdn6.site-media.eu/images/576%2C1160x772%2B134%2B41/18694509/coachRoy-LCXiB9ufGNk2uXEnykijBA.webp)]">
                        <div class="w-3 h-3 bg-green-500 animate-ping rounded-full absolute left-0 top-0"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full absolute left-0 top-0"></div>
                    </div>
                    <div>
                        <h2 class="text-xs text-black font-semibold">Roy Koenders</h2>
                        <h3 class="text-xs text-black/50 font-medium">Jouw coach</h3>
                    </div>
                </div>
            </div>
        @else
            {{-- Fallback als er (nog) geen coach gekoppeld is --}}
            <div class="min-w-[250px] max-w-[200px] h-fit p-6 bg-white rounded-3xl mt-[6.5rem]">
                <h2 class="text-xs text-black font-semibold">Nog geen coach gekoppeld</h2>
                <p class="text-xs text-black/50">Neem contact op met support.</p>
            </div>
        @endif
    @endif
    <div class="flex-1">
        @if($role === 'client')
            <a href="/client/threads" class="text-xs opacity-25 hover:opacity-50 transition duration-300">Terug naar overzicht</a>
        @elseif($role === 'coach')
            <a href="/coach/threads" class="text-xs opacity-25 hover:opacity-50 transition duration-300">Terug naar overzicht</a>
        @endif
        <h1 class="text-2xl font-bold mb-8 mt-4">
            <i class="fa-solid fa-file-signature mr-4"></i>
            {{ $thread->subject }}
        </h1>

        <div class="w-full p-6 bg-white rounded-3xl min-h-[400px]">
            <div class="flex flex-col gap-4">
                @foreach($thread->messages as $m)
                    @php
                        // Bepaal of afzender coach is
                        $isCoach = optional($m->sender)->hasRole
                            ? optional($m->sender)->hasRole('coach')
                            : (bool) data_get($m->sender, 'coach'); // fallback als hasRole() niet bestaat
                    @endphp

                    <div class="flex {{ $isCoach ? 'justify-end' : 'justify-start' }}">
                        <div class="rounded-2xl p-[1.5rem] min-w-[80%] max-w-[80%] {{ $isCoach ? 'bg-[#c8ab7a]/20 text-gray-900' : 'bg-gray-100 text-gray-900' }}">
                            <div class="text-sm mb-3">
                                {{ $m->body }}
                            </div>
                            <div class="text-xs font-semibold {{ $isCoach ? 'text-gray-800' : 'text-gray-500' }}">
                                {{ $m->created_at->format('d-m-Y H:i') }}<br>{{ $m->sender->name ?? 'User #'.$m->sender_id }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @can('reply', $thread)
        <form method="POST"
            action="{{ $role==='coach' ? route('coach.threads.messages.store',$thread) : route('client.threads.messages.store',$thread) }}"
            class="mt-4 w-full">
            @csrf
            <textarea
                name="body" rows="3"
                class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm"
                placeholder="Typ je bericht..."></textarea>
            <button class="mt-4 px-6 py-3 bg-[#c8ab7a] text-white font-medium text-sm rounded">Versturen</button>
        </form>
        @endcan
    </div>
</div>
@endsection
