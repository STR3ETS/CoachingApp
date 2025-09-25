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

<div x-data class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold">
                {{ $title ?? 'Trainingsschema' }}
                <span class="ml-2 text-xs px-2 py-0.5 rounded border {{ $isFinal ? 'border-green-300 text-green-700 bg-green-50' : 'border-amber-300 text-amber-700 bg-amber-50' }}">
                    {{ $isFinal ? 'Definitief' : 'Concept' }}
                </span>
            </h2>
            @if($weeksCount)
                <p class="text-xs text-gray-500 mt-1">{{ $weeksCount }} weken</p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @if(!$isFinal && $__editUrl)
                <a href="{{ $__editUrl }}"
                    class="px-3 py-1.5 text-xs rounded border bg-amber-50 border-amber-300 text-amber-800 hover:bg-amber-100">
                    Bewerken
                </a>
            @endif
            <button onclick="window.print()" class="px-3 py-1.5 text-xs rounded border">Print</button>
            <a href="{{ url()->current() }}?format=json" class="px-3 py-1.5 text-xs rounded border">Download JSON</a>
        </div>
    </div>

    {{-- Weektegels --}}
    <div class="grid grid-cols-1 gap-4">
        @forelse($weeks as $weekKey => $week)
            <section class="bg-white border rounded-lg p-4" x-data="{ open:true }">
                <header class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <h3 class="font-medium flex items-center gap-2">
                        {{ ucfirst(str_replace('_',' ', $weekKey)) }}
                        @if(!empty($weekRanges[$weekKey]))
                            <span class="text-xs px-2 py-0.5 rounded border bg-gray-50 text-gray-700">
                                {{ $weekRanges[$weekKey] }}
                            </span>
                        @endif
                    </h3>
                    <span class="text-xs text-gray-500" x-text="open ? 'verberg' : 'toon'"></span>
                </header>

                @php
                    $focus = data_get($week, 'focus');
                    $sessions = is_array(data_get($week, 'sessions')) ? data_get($week, 'sessions') : [];
                @endphp

                <div x-show="open" x-collapse class="mt-3 space-y-2">
                    @if($focus)
                        <div class="text-xs px-2 py-1 rounded bg-gray-50 border inline-block">Focus: {{ $focus }}</div>
                    @endif

                    <ul class="space-y-2">
                        @forelse($sessions as $idx => $s)
                            @php
                                $sessionNote = trim((string) data_get($s, 'note', ''));
                            @endphp
                            <li class="p-3 rounded border">
                                <div class="text-sm font-medium">
                                    {{ data_get($s,'day','Sessie') }}
                                    @php $ex = is_array(data_get($s,'exercises')) ? data_get($s,'exercises') : []; @endphp
                                    @if(!empty($ex))
                                        <span class="text-xs text-gray-500">— {{ count($ex) }} oefeningen</span>
                                    @endif
                                </div>

                                {{-- ▼ Nieuw: sessie-notitie tonen --}}
                                @php $sessionNote = trim((string) data_get($s, 'note', '')); @endphp
                                @if($sessionNote !== '')
                                    <div class="mt-2 text-xs px-2 py-1 rounded border bg-yellow-50 text-yellow-800">
                                        <span class="font-semibold">Sessienotitie:</span>
                                        {{ $sessionNote }}
                                    </div>
                                @endif
                                {{-- ▲ Einde nieuw --}}

                                @if(!empty($ex))
                                    <div class="mt-2 overflow-x-auto">
                                        <table class="min-w-full text-xs border table-fixed">
                                            <colgroup>
                                                <col class="w-[40%]">
                                                <col class="w-[10%]">
                                                <col class="w-[22%]">
                                                <col class="w-[10%]">
                                                <col class="w-[18%]">
                                            </colgroup>
                                            <thead class="bg-gray-50">
                                                <tr class="border-b">
                                                    <th class="text-left p-2 align-middle">Oefening</th>
                                                    <th class="text-right p-2 align-middle">Sets</th>
                                                    <th class="text-left  p-2 align-middle">Reps</th>
                                                    <th class="text-right p-2 align-middle">RPE</th>
                                                    <th class="text-left  p-2 align-middle">Notities</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($ex as $e)
                                                    <tr class="border-t">
                                                        <td class="p-2 align-middle whitespace-nowrap">
                                                            {{ data_get($e,'name') }}
                                                        </td>
                                                        <td class="p-2 align-middle text-right tabular-nums whitespace-nowrap">
                                                            {{ data_get($e,'sets') }}
                                                        </td>
                                                        <td class="p-2 align-middle whitespace-nowrap">
                                                            {{ data_get($e,'reps') }}
                                                        </td>
                                                        <td class="p-2 align-middle text-right tabular-nums whitespace-nowrap">
                                                            {{ data_get($e,'rpe') }}
                                                        </td>
                                                        <td class="p-2 align-middle whitespace-nowrap truncate"
                                                            title="{{ data_get($e,'notes') }}">
                                                            {{ data_get($e,'notes') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
