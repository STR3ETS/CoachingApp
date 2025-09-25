<!doctype html>
<html lang="nl" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Hyrox Coaching'))</title>

    {{-- Vite assets (Breeze/Tailwind/Alpine entrypoints) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine (CDN) voor de intake-UI, als je Alpine al via app.js bundelt mag deze weg --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://kit.fontawesome.com/4180a39c11.js" crossorigin="anonymous"></script>

    {{-- Extra head-injectie per pagina --}}
    @yield('head')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen max-h-screen overflow-y-hidden flex bg-[#c8ab7a]/10 text-gray-900 antialiased">
    {{-- Site header / nav --}}
    <header class="bg-[#c8ab7a]">
        <div class="max-w-6xl mx-auto p-1 md:p-3 flex items-center justify-between flex-col">
            <a href="{{ route('landing') }}" class="flex items-center gap-2 font-semibold tracking-tight mb-10">
                <img class="max-w-[1rem] md:max-w-[2rem] mt-2 md:mt-0" src="/assets/2befit-logo.png" alt="Logo">
            </a>

            <nav class="flex flex-col items-center gap-2 text-sm">
                @php
                    ($role = auth()->user()->role ?? 'client')
                @endphp
                @auth
                    @if($role === 'coach')
                        <a href="{{ url('/coach') }}" class="relative w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg hover:bg-[#947d57] group">
                            <i class="fa-solid fa-house text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                            <span class="absolute left-full ml-2 px-2 py-1 text-xs rounded-lg bg-white border border-[#d1d1d1] text-gray-900 opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">
                                Overzicht
                            </span>
                        </a>
                        <a href="{{ url('/coach/clients/unassigned') }}" class="relative w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg hover:bg-[#947d57] group">
                            <i class="fa-solid fa-ban text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                            <span class="absolute left-full ml-2 px-2 py-1 text-xs rounded-lg bg-white border border-[#d1d1d1] text-gray-900 opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">
                                Ongeclaimde klanten
                            </span>
                        </a>
                        <a href="{{ url('/coach/threads') }}" class="relative w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg hover:bg-[#947d57] group">
                            <i class="fa-solid fa-messages text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                            <span class="absolute left-full ml-2 px-2 py-1 text-xs rounded-lg bg-white border border-[#d1d1d1] text-gray-900 opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">
                                Chat met je klanten
                            </span>
                        </a>
                    @elseif($role === 'client')
                        <a href="{{ url('/client') }}" class="relative w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg hover:bg-[#947d57] group">
                            <i class="fa-solid fa-house text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                            <span class="absolute left-full ml-2 px-2 py-1 text-xs rounded-lg bg-white border border-[#d1d1d1] text-gray-900 opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">
                                Overzicht
                            </span>
                        </a>
                        <a href="{{ url('/client/plan') }}" class="relative w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg hover:bg-[#947d57] group">
                            <i class="fa-solid fa-clipboard-user text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                            <span class="absolute left-full ml-2 px-2 py-1 text-xs rounded-lg bg-white border border-[#d1d1d1] text-gray-900 opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">
                                Weekplanning
                            </span>
                        </a>
                        <a href="{{ url('/client/threads') }}" class="relative w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg hover:bg-[#947d57] group">
                            <i class="fa-solid fa-messages text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                            <span class="absolute left-full ml-2 px-2 py-1 text-xs rounded-lg bg-white border border-[#d1d1d1] text-gray-900 opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">
                                Chat met je coach
                            </span>
                        </a>
                        <a href="https://www.2befitsupplements.nl" target="_blank" class="relative w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg hover:bg-[#947d57] group">
                            <i class="fa-solid fa-shopping-bag text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                            <span class="absolute left-full ml-2 px-2 py-1 text-xs rounded-lg bg-white border border-[#d1d1d1] text-gray-900 opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">
                                Supplementen
                            </span>
                        </a>
                    @endif
                @endauth
                @auth
                    <form class="absolute bottom-3" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg group">
                            <i class="fa-solid fa-right-from-bracket text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                        </button>
                    </form>
                @else
                    <a href="{{ route('intake.start') }}" class="relative w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg hover:bg-[#947d57] group">
                        <i class="fa-solid fa-bolt text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                        <span class="absolute left-full ml-2 px-2 py-1 text-xs rounded-lg bg-white border border-[#d1d1d1] text-gray-900 opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">
                            Intakeformulier
                        </span>
                    </a>
                    <a href="{{ route('login') }}" class="relative w-8 h-8 flex items-center justify-center transition duration-300 rounded-lg hover:bg-[#947d57] group">
                        <i class="fa-solid fa-right-to-bracket text-gray-900 group-hover:text-[#fff] transition duration-300"></i>
                        <span class="absolute left-full ml-2 px-2 py-1 text-xs rounded-lg bg-white border border-[#d1d1d1] text-gray-900 opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">
                            Inloggen
                        </span>
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Flash/status/toasts --}}
    <div class="fixed z-[999] top-4 right-4">
        @if (session('status'))
            <div x-data="{ show:true }" x-show="show" x-init="setTimeout(()=>show=false,4000)"
                 class="mt-4 rounded-md border border-green-200 bg-green-50 text-green-900 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 text-red-900 px-4 py-3">
                <div class="font-semibold text-sm mb-1">Er ging iets mis:</div>
                <ul class="list-disc list-inside text-sm space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Main content slot --}}
    <div class="max-h-screen flex-1 overflow-y-auto">
        <main class="py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Pagina-specifieke scripts --}}
    @stack('scripts')
</body>
</html>