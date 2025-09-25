@props([
  'weeks' => [], 'title' => null, 'isFinal' => false, 'weeksCount' => null,
  'editUrl' => null,
  'startDate' => null, // 👈 nieuw, verwacht 'YYYY-MM-DD'
])
@php
    // Gebruik meegestuurde $editUrl, anders gok de edit-route van de huidige {plan}
    $__editUrl = $editUrl ?? (
        optional(request()->route('plan'))->id
            ? route('coach.plans.edit', request()->route('plan'))
            : null
    );

    use Carbon\Carbon;

    $weeks = is_array($weeks) ? $weeks : [];

    $routePlan = request()->route('plan');
    $base = null;

    if ($routePlan && !empty($routePlan->start_date)) {
        $base = Carbon::parse($routePlan->start_date)->startOfDay();
    } else {
        $base = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    $weekRanges = [];
    foreach ($weeks as $weekKey => $weekData) {
        $num = (int)preg_replace('/\D+/', '', (string)$weekKey); // "week_2" → 2
        $start = (clone $base)->addWeeks(max(0, $num - 1));
        $end   = (clone $start)->addDays(6);

        $start->locale('nl_NL'); $end->locale('nl_NL');
        $weekRanges[$weekKey] = $start->isoFormat('D MMM') . ' – ' . $end->isoFormat('D MMM YYYY');
    }
@endphp

<div x-data class="flex flex-col gap-4">
    <div class="flex items-center justify-between -mb-2">
        <div class="">
            <h2 class="text-lg font-bold flex items-center">
                {{ $title ?? 'Trainingsschema' }}
                <span class="ml-2 inline-block w-2 h-2 rounded-full animate-pulse 
                    {{ $isFinal ? 'bg-green-500' : 'bg-amber-500' }}">
                </span>
            </h2>
        </div>
        <div class="flex items-center gap-2">
            @if(!$isFinal && $__editUrl)
                <a href="{{ $__editUrl }}"
                    class="px-3 py-1.5 text-xs rounded border bg-amber-50 border-amber-300 text-amber-800 hover:bg-amber-100">
                    Bewerken
                </a>
            @endif
            <button onclick="window.print()" class="px-3 py-1.5 text-xs rounded bg-black text-white font-semibold">Print</button>
            <a href="{{ url()->current() }}?format=json" class="px-3 py-1.5 text-xs rounded bg-black text-white font-semibold">Download JSON</a>
        </div>
    </div>

    {{-- Weektegels --}}
    <div class="grid grid-cols-1 gap-4">
        @forelse($weeks as $weekKey => $week)
            <section class="p-6 bg-white rounded-3xl border" x-data="{ open:true }">
                <header class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <h3 class="font-semibold flex flex-col gap-4">
                        {{ ucfirst(str_replace('_',' ', $weekKey)) }}
                    </h3>
                    <span class="text-xs text-gray-500 inline-flex items-center gap-1" role="img" aria-label="" :title="open ? 'Verberg' : 'Toon'">
                        <i :class="open ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        <span class="sr-only" x-text="open ? 'Verberg' : 'Toon'"></span>
                    </span>
                </header>

                @php
                    $focus = data_get($week, 'focus');
                    $sessions = is_array(data_get($week, 'sessions')) ? data_get($week, 'sessions') : [];
                @endphp

                <div x-show="open" x-collapse class="mt-3 flex flex-col gap-2">
                    @if(!empty($weekRanges[$weekKey]))
                        <span class="w-fit text-xs px-2 py-1 font-semibold flex items-center rounded border border-gray-200 bg-gray-100 text-gray-500">
                            {{ $weekRanges[$weekKey] }}
                        </span>
                    @endif
                    @if($focus)
                        <div class="w-fit text-xs px-2 py-1 font-semibold rounded border border-[#c8ab7a]/25 bg-[#c8ab7a]/20 text-[#c8ab7a] mb-4">Focus: {{ $focus }}</div>
                    @endif

                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($sessions as $idx => $s)
                            @php
                                $ex = is_array(data_get($s,'exercises')) ? data_get($s,'exercises') : [];
                                $sessionNote = trim((string) data_get($s, 'note', ''));
                            @endphp

                            <li class="p-4 bg-gray-50 border rounded-xl">
                                <div class="text-sm font-medium flex items-center justify-between -mb-2">
                                    {{ data_get($s,'day','Sessie') }}
                                </div>

                                {{-- Sessie-notitie --}}
                                @if($sessionNote !== '')
                                    <div class="mt-2">
                                        <span class="text-[12px] text-gray-500 font-semibold">Notitie voor de sessie van {{ $s['day'] ?? 'Sessie' }}:</span>
                                        <p class="text-xs leading-relaxed italic">
                                            "{{ $sessionNote }}"
                                        </p>
                                    </div>
                                @endif

                                @if(!empty($ex))
                                    <div class="mt-4 space-y-3">
                                        @foreach($ex as $e)
                                            @php
                                                $name  = trim((string) data_get($e,'name','Oefening'));
                                                $sets  = data_get($e,'sets');
                                                $reps  = trim((string) data_get($e,'reps',''));
                                                $rpe   = trim((string) data_get($e,'rpe',''));
                                                $notes = trim((string) data_get($e,'notes',''));
                                            @endphp

                                            <div class="p-3 rounded-xl flex flex-col justify-between border bg-white/90 min-h-[115px] gap-4">
                                                <div class="flex flex-wrap items-center justify-between gap-2 relative">
                                                    <div class="text-sm font-semibold">
                                                        <i class="fa-solid fa-arrow-down rotate-[-90deg] mr-2"></i>
                                                        {{ $name }}
                                                    </div>

                                                    <div class="flex flex-col items-end gap-2 text-[12px] absolute z-1 right-0 top-0">
                                                        <span class="w-fit px-2 py-0.5 rounded font-semibold border border-purple-200 bg-purple-100">
                                                            <span class="text-purple-500">Sets:</span>
                                                            <span class="text-purple-500">{{ $sets ?? '-' }}</span>
                                                        </span>
                                                        <span class="w-fit px-2 py-0.5 rounded font-semibold border border-orange-200 bg-orange-100">
                                                            <span class="text-orange-500">Reps:</span>
                                                            <span class="text-orange-500">{{ $reps !== '' ? $reps : '-' }}</span>
                                                        </span>
                                                        <span class="w-fit px-2 py-0.5 rounded font-semibold border border-gray-200 bg-gray-100">
                                                            <span class="text-gray-500">RPE:</span>
                                                            <span class="text-gray-500">{{ $rpe !== '' ? $rpe : '-' }}</span>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="max-w-[50%]">
                                                    <div class="text-[12px] text-gray-500 font-semibold">Notitie over deze oefening:</div>
                                                    <p class="text-xs leading-relaxed italic">
                                                        "{{ $notes !== '' ? $notes : '—' }}"
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">Geen oefeningen toegevoegd.</p>
                                @endif
                            </li>
                        @empty
                            <li class="text-xs text-gray-500">Geen sessies voor deze week.</li>
                        @endforelse
                    </ul>
                </div>
            </section>
        @empty
            <div class="col-span-full text-sm text-gray-500">
                Geen weekschema gevonden in dit plan.
            </div>
        @endforelse
    </div>
</div>

{{-- Print CSS --}}
<style>
@media print {
    header, nav, footer { display: none !important; }
    .border { border-color: #ddd !important; }
    .rounded-lg { break-inside: avoid; }
    a[href]:after { content: ""; }
    body { background: white !important; }
}
</style>
