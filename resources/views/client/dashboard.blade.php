@extends('layouts.app')
@section('title','Mijn dashboard')

@section('content')
<h1 class="text-2xl font-bold mb-2">Goededag {{ auth()->user()->name }}! 👋</h1>
<p class="text-sm text-black opacity-80 font-medium mb-10">Welkom op jouw persoonlijke training omgeving.<br>Zie hier jouw trainingsschema, chat met je coach of bekijk onze supplementen.</p>

@php
    $client = optional(auth()->user())->client;
    $hasWeighInToday = $client
        ? $client->weighIns()->whereDate('date', now()->toDateString())->exists()
        : false;
@endphp

@if(!$hasWeighInToday)
    <div class="mb-12 p-3 rounded-2xl border border-red-200 bg-red-50 text-red-900 flex items-center justify-between">
        <div class="text-xs">
            <span class="font-semibold"><i class="fa-solid fa-circle-exclamation"></i>&nbsp;&nbsp;Reminder:</span> Vergeet niet je weigh-in te doen vandaag!
        </div>
        <a href="{{ request()->fullUrlWithQuery(['weigh_in' => 1]) }}"
           class="px-3 py-1.5 text-xs rounded bg-[#000] text-white hover:bg-[#a89067] transition duration-300 font-semibold">
           Geef je gewicht op
        </a>
    </div>
@endif

{{-- Snelkoppelingen --}}
<h2 class="text-lg font-bold mb-2">Snelkoppelingen</h2>
<div class="grid gap-3 grid-cols-1 sm:grid-cols-4 mb-6">
    <a href="{{ route('client.plan.show') }}"
       class="p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded">
        <div class="font-semibold text-white">Mijn trainingsplan</div>
        <p class="text-sm text-white">Bekijk je trainingsplan</p>
    </a>

    <a href="{{ route('client.threads.create') }}"
       class="p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded">
        <div class="font-semibold text-white">Nieuwe chat met coach</div>
        <p class="text-sm text-white">Start een nieuw gesprek</p>
    </a>

    <a href="{{ route('client.threads.index') }}"
       class="p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded">
        <div class="font-semibold text-white">Alle gesprekken met coach</div>
        <p class="text-sm text-white">Overzicht & zoeken</p>
    </a>

    @if(!isset($activeSub) || !$activeSub)
        <a href="{{ route('intake.start') }}"
           class="p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded">
            <div class="font-semibold text-white">Start intake</div>
            <p class="text-sm text-white">Begin met je abonnement</p>
        </a>
    @else
        <a href="{{ route('client.plan.show') }}#week-vandaag"
           class="p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded">
            <div class="font-semibold text-white">Training van vandaag</div>
            <p class="text-sm text-white">Spring direct naar vandaag</p>
        </a>
    @endif
</div>

{{-- Metingen & Weegmoment --}}
@php
    $client  = optional(auth()->user())->client;
    $profile = optional($client)->profile;

    // Laatste 2 wegingen
    $lastTwo = $client
        ? $client->weighIns()->orderByDesc('date')->limit(2)->get()
        : collect();

    $latestWeighIn = $lastTwo->get(0);
    $prevWeighIn   = $lastTwo->get(1);

    // Toon gewicht (val terug op intake-profiel)
    $weight = $latestWeighIn->weight_kg ?? $profile->weight_kg ?? null;
    $height = $profile->height_cm ?? null;
    $bmi    = ($weight && $height) ? round($weight / pow($height/100, 2), 1) : null;

    // === BMI indicatie (schaal + label + pijl) ===
    $bmiCategory = null;
    $bmiCatClass = 'bg-gray-500';
    if ($bmi !== null) {
        if ($bmi < 18.5) { $bmiCategory = 'Ondergewicht';  $bmiCatClass = 'bg-blue-500'; }
        elseif ($bmi < 25) { $bmiCategory = 'Gezond gewicht'; $bmiCatClass = 'bg-green-600'; }
        elseif ($bmi < 30) { $bmiCategory = 'Overgewicht';    $bmiCatClass = 'bg-yellow-600'; }
        else { $bmiCategory = 'Obesitas'; $bmiCatClass = 'bg-red-600'; }
    }
    // Positie op gekleurde schaal (15 → 40)
    $bmiMin = 15; $bmiMax = 40;
    $bmiClamped = $bmi !== null ? max($bmiMin, min($bmiMax, $bmi)) : null;
    $bmiPct = $bmiClamped !== null ? round(($bmiClamped - $bmiMin) / ($bmiMax - $bmiMin) * 100, 1) : null;

    // Δ-berekening
    $deltaKg = null;
    $deltaSinceLabel = null;

    if ($latestWeighIn && $prevWeighIn) {
        // Normaal: laatste vs vorige weging
        $deltaKg = round($latestWeighIn->weight_kg - $prevWeighIn->weight_kg, 1);
        $days = \Carbon\Carbon::parse($prevWeighIn->date)->diffInDays(\Carbon\Carbon::parse($latestWeighIn->date));
        $deltaSinceLabel = ($days >= 6 && $days <= 8) ? 'sinds vorige week' : 'sinds vorige weging';
    } elseif ($latestWeighIn && $profile?->weight_kg) {
        // Eerste echte weging: vergelijk met intake/startgewicht
        $deltaKg = round($latestWeighIn->weight_kg - $profile->weight_kg, 1);
        $deltaSinceLabel = 't.o.v. intake';
    }

    // Presentatie helpers
    function deltaText($deltaKg) {
        if ($deltaKg === null) return null;
        $abs = number_format(abs($deltaKg), 1, ',', '.');
        return ($deltaKg < 0 ? '−' : ($deltaKg > 0 ? '+' : '±')) . $abs . ' kg';
    }
    function deltaClass($deltaKg) {
        if ($deltaKg === null) return 'text-gray-500';
        if ($deltaKg < 0) return 'text-green-600';
        if ($deltaKg > 0) return 'text-red-600';
        return 'text-gray-600';
    }
@endphp
<h2 class="text-lg font-bold mb-2">Informatie</h2>
<section class="grid gap-4 grid-cols-2">
    {{-- Huidig gewicht --}}
    <div class="p-6 bg-white rounded-3xl border">
        <div class="text-sm text-black font-semibold opacity-50 mb-1">Huidig gewicht</div>
        <div class="text-3xl font-bold text-[#c8ab7a] mb-4">
            {{ $weight ? number_format($weight,1,',','.') . ' kg' : '—' }}
        </div>
        {{-- Δ sinds vorige week / vorige weging --}}
        @if(!is_null($deltaKg))
            <div class="text-xs font-semibold {{ deltaClass($deltaKg) }}">
                {{ deltaText($deltaKg) }} {{ $deltaSinceLabel }}
            </div>
        @endif
        @if($latestWeighIn)
            <div class="text-[12px] text-gray-500 mt-1">
                Laatste weging: {{ \Carbon\Carbon::parse($latestWeighIn->date)->format('d-m-Y') }}
            </div>
        @endif

    </div>

    {{-- BMI --}}
    <div class="p-6 bg-white rounded-3xl border">
        <div class="text-sm text-black font-semibold opacity-50 mb-1">BMI (Body Mass Index)</div>

        {{-- GETAL + CATEGORIE NAAST ELKAAR --}}
        @if($bmi !== null)
            <div class="flex items-center gap-2 mb-[1.35rem]">
                <div class="text-3xl font-bold text-[#c8ab7a] tabular-nums">
                    {{ number_format($bmi, 1, ',', '.') }}
                </div>
                <span class="px-2 py-1 rounded font-semibold text-white text-[10px] {{ $bmiCatClass }}">
                    {{ $bmiCategory }}
                </span>
            </div>
        @else
            <div class="text-3xl font-bold text-[#c8ab7a]">—</div>
        @endif
        @if($bmi !== null)
            {{-- Gekleurde schaal + pijltje --}}
            <div class="mt-3">
                <div class="relative h-2 rounded bg-gradient-to-r from-green-500 via-yellow-400 to-red-500">
                    @if($bmiPct !== null)
                        {{-- Marker/pijltje --}}
                        <div class="absolute -top-6" style="left: calc({{ $bmiPct }}% - 6px);">
                            <i class="fa-solid fa-arrow-down fa-xs"></i>
                        </div>
                    @endif
                </div>

                {{-- Tickmarks & labels: elke 5 BMI-punten (zonder streepje op eerste/laatste) --}}
                <div class="relative mt-2 text-[10px] text-gray-600 overflow-visible">
                    @php
                        $tickStep = 5;
                        $start    = ceil($bmiMin / $tickStep) * $tickStep;
                        $ticks    = [];
                        for ($v = $start; $v <= $bmiMax; $v += $tickStep) {
                            $ticks[] = $v;
                        }
                    @endphp

                    <div class="relative h-4">
                        @foreach($ticks as $v)
                            @php
                                $p = (($v - $bmiMin) / ($bmiMax - $bmiMin)) * 100;
                            @endphp
                            <div class="absolute -top-4" style="left: {{ $p }}%;">
                                <div class="flex flex-col items-center -translate-x-1/2">
                                    {{-- Streepje alleen tonen als het NIET de eerste of laatste is --}}
                                    @if(!$loop->first && !$loop->last)
                                        <div class="w-px h-2 bg-gray-500"></div>
                                    @else
                                        {{-- Spacer zodat het label netjes blijft uitgelijnd --}}
                                        <div class="w-px h-2 opacity-0"></div>
                                    @endif
                                    <div class="mt-2 text-gray-500 leading-none font-semibold">{{ $v }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="text-xs text-gray-500 mt-1">Geen BMI-berekening mogelijk (ontbrekende lengte/gewicht).</div>
        @endif
    </div>
</section>
{{-- Globale weigh-in modal (alleen via banner / errors) --}}
<div x-data="{ openWeighIn: {{ ($errors->any() || request()->boolean('weigh_in')) ? 'true' : 'false' }} }">
    <div x-show="openWeighIn"
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center"
         aria-modal="true" role="dialog">
        {{-- backdrop --}}
        <div class="absolute inset-0 bg-black/40" @click="openWeighIn = false"></div>

        {{-- panel --}}
        <div x-transition
             class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-xl border">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h3 class="font-semibold">Nieuwe weging</h3>
                <button class="text-gray-500 hover:text-gray-800"
                        @click="openWeighIn = false"
                        aria-label="Sluiten">✕</button>
            </div>

            <form method="POST" action="{{ route('client.weighins.store') }}" class="p-5 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Datum</label>
                    <input type="date" name="date"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#c8ab7a]"
                           value="{{ old('date', now()->toDateString()) }}">
                </div>

                <div>
                    <label class="block text-xs text-gray-600 mb-1">Gewicht (kg)</label>
                    <input type="number" step="0.1" min="20" max="400"
                           name="weight_kg"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#c8ab7a]"
                           placeholder="bijv. 78.5"
                           value="{{ old('weight_kg', $profile->weight_kg) }}">
                </div>

                <div>
                    <label class="block text-xs text-gray-600 mb-1">Notitie (optioneel)</label>
                    <input type="text" name="notes"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#c8ab7a]"
                           placeholder="bv. ochtend, nuchter"
                           value="{{ old('notes') }}">
                </div>

                @if ($errors->any())
                    <div class="text-xs text-red-600">
                        @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
                    </div>
                @endif

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" class="px-3 py-2 border rounded"
                            @click="openWeighIn = false">Annuleren</button>
                    <button class="px-4 py-2 bg-black text-white rounded">Opslaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-4 mt-4">
    {{-- Training deze week --}}
    @php
        use Carbon\Carbon;
        use App\Models\TrainingPlan;

        $__plan = request()->route('plan') ?? ($plan ?? ($currentPlan ?? null));

        if (!$__plan) {
            $clientId = optional(optional(auth()->user())->client)->id;
            if ($clientId) {
                $__plan = TrainingPlan::where('client_id', $clientId)->latest()->first();
            }
        }

        $rawWeeks = optional($__plan)->plan_json;
        $__weeks = is_array($rawWeeks)
            ? $rawWeeks
            : (is_string($rawWeeks) ? (json_decode($rawWeeks, true) ?: []) : []);
    @endphp

    @if($__plan && !empty($__weeks))
        @php
            // Als het plan een start_date heeft → gebruik die als week_1 start,
            // anders: huidige week (maandag) = week_1
            $base = !empty($__plan->start_date)
                ? Carbon::parse($__plan->start_date)->startOfDay()
                : Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();

            // Toon week_1 (niet schuiven met diff)
            $idx     = 0;
            $weekNum = 1;
            $weekKey = "week_{$weekNum}";
            $weekData = $__weeks[$weekKey] ?? null;

            $weekStart = (clone $base);
            $weekEnd   = (clone $base)->addDays(6);

            $weekStart->locale('nl_NL'); $weekEnd->locale('nl_NL');
            $range = $weekStart->isoFormat('D MMM') . ' – ' . $weekEnd->isoFormat('D MMM YYYY');
        @endphp

        <section class="md:col-span-3 p-6 bg-white rounded-3xl border" x-data>
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold">Training deze week</h2>
            </div>

            @if($weekData)
                <div class="text-sm flex flex-col gap-2 mb-6">
                    <div class="w-fit text-xs px-2 py-1 font-semibold flex items-center rounded border border-gray-200 bg-gray-100 text-[#c8ab7a]">
                        <span class="text-gray-500 font-semibold">Week {{ $weekNum }}</span>
                        <span class="text-gray-500 text-[10px] ml-2">({{ $range }})<br></span>
                    </div>
                    @if(!empty($weekData['focus']))
                        <span class="w-fit text-xs px-2 py-1 font-semibold rounded border border-[#c8ab7a]/25 bg-[#c8ab7a]/20 text-[#c8ab7a]">Focus: {{ $weekData['focus'] }}</span>
                    @endif
                </div>

                @php $sessions = is_array($weekData['sessions'] ?? null) ? $weekData['sessions'] : []; @endphp
                @php
                    $logs = \App\Models\TrainingSessionLog::where('client_id', optional(optional(auth()->user())->client)->id)
                        ->where('plan_id', $__plan->id)
                        ->where('week_number', $weekNum)
                        ->get()
                        ->keyBy('session_index');
                @endphp
                @if(count($sessions))
                    <ul class="grid grid-cols-2 gap-4">
                        @foreach($sessions as $s)
                            @php $ex = is_array($s['exercises'] ?? null) ? $s['exercises'] : []; @endphp
                            <li class="p-4 bg-gray-50 border rounded-xl">
                                <div class="text-sm font-medium flex items-center justify-between">
                                    {{ $s['day'] ?? 'Sessie' }}

                                    @php $i = $loop->index; $log = $logs->get($i); @endphp

                                    @if($log)
                                        <span class="text-xs text-green-500 font-semibold">
                                            Training voltooid <i class="ml-2 fa-solid fa-toggle-on fa-xl"></i>
                                        </span>
                                    @else
                                        <button
                                            class="text-xs opacity-50 hover:opacity-100 transition duration-300 font-semibold"
                                            @click="$dispatch('open-session-log', {
                                                planId: {{ $__plan->id }},
                                                weekNumber: {{ $weekNum }},
                                                sessionIndex: {{ $i }},
                                                sessionDay: @js($s['day'] ?? null),
                                                isEdit: false
                                            })">
                                            Markeer als afgerond <i class="ml-2 fa-solid fa-toggle-off fa-xl opacity-50"></i>
                                        </button>
                                    @endif
                                </div>

                                @php $sessionNote = trim((string) data_get($s, 'note', '')); @endphp
                                @if($sessionNote !== '')
                                    <div>
                                        <span class="text-[12px] text-gray-500 font-semibold">Notitie voor de sessie van {{ $s['day'] ?? 'Sessie' }}:</span>
                                        <p class="text-xs leading-relaxed italic">
                                            "{{ $sessionNote }}"
                                        </p>
                                    </div>
                                @endif

                                @if($ex)
                                    <div class="mt-4 space-y-3">
                                        @foreach($ex as $e)
                                            @php
                                                $name  = trim((string)($e['name'] ?? ''));
                                                $sets  = $e['sets'] ?? null;
                                                $reps  = trim((string)($e['reps'] ?? ''));
                                                $rpe   = trim((string)($e['rpe'] ?? ''));
                                                $notes = trim((string)($e['notes'] ?? ''));
                                            @endphp

                                            <div class="p-3 rounded-xl flex flex-col justify-between border bg-white/90 min-h-[115px] gap-4">
                                                <div class="flex flex-wrap items-center justify-between gap-2 relative">
                                                    <div class="text-sm font-semibold">
                                                        <i class="fa-solid fa-arrow-down rotate-[-90deg] mr-2"></i>
                                                        {{ $name !== '' ? $name : 'Oefening' }}
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

                                                {{-- Notitie als ruim tekstblok, met regelafbrekingen behouden --}}
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
                                    <p class="mt-1 text-xs text-gray-500">Geen oefeningen in deze sessie.</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">Geen sessies voor deze week.</p>
                @endif
            @else
                <p class="text-sm text-gray-500">Geen weekgegevens gevonden in je plan.</p>
            @endif
        </section>
    @endif

    {{-- Abonnement kaart --}}
    <section class="md:col-span-1 bg-white rounded-3xl p-6 border">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Jouw abonnement</h2>
            @if($activeSub)
                <div class="flex items-center gap-2 animate-pulse">
                    <p class="text-green-500 font-semibold text-[12px]">Actief</p>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
            @endif
        </div>

        @if(isset($activeSub) && $activeSub)
        <div class="w-full flex flex-col">
            <div class="w-full flex items-center justify-between">
                <h3 class="text-sm text-black font-semibold opacity-50">Begindatum</h3>
                <p class="text-sm text-black font-semibold">{{ optional($activeSub->starts_at)->format('d-m-Y') ?? '—' }}</p>
            </div>
            <hr class="my-3">
            <div class="w-full flex items-center justify-between">
                <h3 class="text-sm text-black font-semibold opacity-50">Einddatum</h3>
                <p class="text-sm text-black font-semibold">{{ optional($activeSub->ends_at)->format('d-m-Y') ?? '—' }}</p>
            </div>
        </div>
        @else
            <p class="text-sm">Je hebt nog geen actief abonnement.</p>
            <a href="{{ route('intake.start') }}" class="inline-flex mt-3 px-3 py-2 rounded bg-black text-white text-sm">
                Start intake
            </a>
        @endif
    </section>
</div>

<div x-data="sessionLogModal()" x-on:open-session-log.document="openModal($event.detail)">
  <div x-show="open"
       x-transition.opacity
       class="fixed inset-0 z-[60] flex items-center justify-center"
       aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

    <div x-transition class="relative z-10 w-full max-w-lg bg-white rounded-3xl p-6">
      <div class="border-b pb-6 flex flex-col relative mb-6">
        <h3 class="font-bold" x-text="isEdit ? 'Sessie bewerken' : 'Sessie afronden'"></h3>
        <p class="text-sm text-black opacity-80 font-medium">Je staat op het punt om je sessie af te ronden.</p>
      </div>
    <button class="text-gray-500 hover:text-gray-800 absolute right-5 top-3" @click="open=false" aria-label="Sluiten">
        <i class="fa-solid fa-xmark"></i>
    </button>

      <form method="POST" action="{{ route('client.session_logs.store') }}" class="flex flex-col gap-4">
        @csrf
        <input type="hidden" name="plan_id"       :value="planId">
        <input type="hidden" name="week_number"   :value="weekNumber">
        <input type="hidden" name="session_index" :value="sessionIndex">
        <input type="hidden" name="session_day"   :value="sessionDay">

        <div>
          <label class="block text-xs text-black mb-1 font-semibold">Wat ging goed?</label>
          <textarea name="went_well" x-model="wentWell" rows="3"
            class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                p-3
                focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                focus:border-[#c8ab7a] text-sm"></textarea>
        </div>

        <div>
          <label class="block text-xs text-black mb-1 font-semibold">Wat ging minder goed?</label>
          <textarea name="went_poorly" x-model="wentPoorly" rows="3"
            class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                p-3
                focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                focus:border-[#c8ab7a] text-sm"></textarea>
        </div>

        <div class="pt-2 flex items-center justify-end gap-2">
          <button type="button" class="px-3 py-2 border rounded" @click="open=false">Annuleren</button>
          <button class="px-4 py-2 bg-black text-white rounded"
                  x-text="isEdit ? 'Opslaan' : 'Afronden'"></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function sessionLogModal(){
  return {
    open:false,
    isEdit:false,
    planId:null, weekNumber:null, sessionIndex:null, sessionDay:null,
    wentWell:'', wentPoorly:'', rpe:null, duration:null, notes:'',
    openModal(d){
      this.isEdit      = !!d.isEdit;
      this.planId      = d.planId;
      this.weekNumber  = d.weekNumber;
      this.sessionIndex= d.sessionIndex;
      this.sessionDay  = d.sessionDay ?? null;
      this.wentWell    = d.wentWell ?? '';
      this.wentPoorly  = d.wentPoorly ?? '';
      this.rpe         = d.rpe ?? null;
      this.duration    = d.duration ?? null;
      this.notes       = d.notes ?? '';
      this.open = true;
    }
  }
}
</script>
@endsection
