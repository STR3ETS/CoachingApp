@extends('layouts.app')
@section('title','Klantdetails')

@section('content')
@php
    use Carbon\Carbon;

    $u   = $client->user;
    $p   = $client->profile;
    $now = Carbon::now()->format('d-m-Y');

    // simpele helpers
    function chip($text, $color = 'gray'){
        $map = [
            'green' => 'bg-green-50 text-green-700 border-green-300',
            'amber' => 'bg-amber-50 text-amber-700 border-amber-300',
            'red'   => 'bg-red-50 text-red-700 border-red-300',
            'gray'  => 'bg-gray-50 text-gray-700 border-gray-300',
        ];
        $cls = $map[$color] ?? $map['gray'];
        return "<span class=\"text-xs px-2 py-0.5 rounded border {$cls}\">{$text}</span>";
    }
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $u->name ?? 'Client #'.$client->id }}</h1>
        <p class="text-sm text-gray-600">{{ $u->email ?? '—' }}</p>
        <div class="mt-2 space-x-2">
            {!! $activeSub ? chip('Abonnement actief','green') : chip('Geen actief abonnement','amber') !!}
            {!! chip('Client-ID: '.$client->id, 'gray') !!}
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('coach.threads.index', ['client' => $client->id]) }}"
           class="px-3 py-1.5 text-xs rounded border">Naar gesprekken</a>
        <a href="{{ route('coach.plans.create', $client) }}"
           class="px-3 py-1.5 text-xs rounded border bg-black text-white">Nieuw plan</a>
    </div>
</div>

{{-- KPI / Info-kaarten --}}
<section class="grid gap-4 grid-cols-1 md:grid-cols-3 mb-6">
    <div class="p-6 bg-white rounded-3xl border">
        <div class="text-sm text-black font-semibold opacity-50 mb-1">Laatste weging</div>
        <div class="text-2xl font-bold text-[#c8ab7a]">
            {{ $latestWeigh? number_format($latestWeigh->weight_kg,1,',','.') . ' kg' : '—' }}
        </div>
        @if(!is_null($deltaKg))
            <div class="text-[12px] mt-1 {{ $deltaKg < 0 ? 'text-green-600' : ($deltaKg > 0 ? 'text-red-600':'text-gray-600') }}">
                @php
                    $sign = $deltaKg < 0 ? '−' : ($deltaKg > 0 ? '+' : '±');
                @endphp
                {{ $sign }}{{ number_format(abs($deltaKg),1,',','.') }} kg sinds vorige weging
            </div>
        @endif
        <div class="text-[12px] text-gray-500 mt-1">
            @if($latestWeigh) {{ Carbon::parse($latestWeigh->date)->format('d-m-Y') }} @endif
        </div>
    </div>

    <div class="p-6 bg-white rounded-3xl border">
        <div class="text-sm text-black font-semibold opacity-50 mb-1">BMI</div>
        <div class="text-2xl font-bold text-[#c8ab7a]">
            {{ $bmi !== null ? number_format($bmi,1,',','.') : '—' }}
        </div>
        <div class="text-[12px] text-gray-500 mt-1">
            Lengte: {{ $p?->height_cm ? $p->height_cm . ' cm' : '—' }}
        </div>
    </div>

    <div class="p-6 bg-white rounded-3xl border">
        <div class="text-sm text-black font-semibold opacity-50 mb-1">Totale betalingen</div>
        <div class="text-2xl font-bold text-[#c8ab7a]">
            € {{ number_format($totalRevenue, 2, ',', '.') }}
        </div>
        <div class="text-[12px] text-gray-500 mt-1">
            T/m {{ $now }}
        </div>
    </div>
</section>

{{-- Tabs --}}
<div x-data="{ tab: 'plans' }" class="space-y-4">
    <div class="flex gap-2">
        <button class="px-3 py-1.5 text-xs rounded border" :class="tab==='plans' ? 'bg-black text-white' : ''" @click="tab='plans'">Schema’s</button>
        <button class="px-3 py-1.5 text-xs rounded border" :class="tab==='intake' ? 'bg-black text-white' : ''" @click="tab='intake'">Intake</button>
        <button class="px-3 py-1.5 text-xs rounded border" :class="tab==='threads' ? 'bg-black text-white' : ''" @click="tab='threads'">Gesprekken</button>
        <button class="px-3 py-1.5 text-xs rounded border" :class="tab==='weigh' ? 'bg-black text-white' : ''" @click="tab='weigh'">Metingen</button>
        <button class="px-3 py-1.5 text-xs rounded border" :class="tab==='payments' ? 'bg-black text-white' : ''" @click="tab='payments'">Betalingen</button>
        <button class="px-3 py-1.5 text-xs rounded border" :class="tab==='subs' ? 'bg-black text-white' : ''" @click="tab='subs'">Abonnement</button>
    </div>

    {{-- TAB: Schema’s --}}
    <section x-show="tab==='plans'" x-transition class="p-6 bg-white rounded-3xl border">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Trainingsschema’s</h2>
            <a href="{{ route('coach.plans.create', $client) }}" class="text-xs underline">Nieuw plan maken</a>
        </div>

        @if($client->trainingPlans->count())
            <ul class="flex flex-col gap-2">
                @foreach($client->trainingPlans as $plan)
                    <li class="p-4 border rounded-xl flex items-center justify-between">
                        <div>
                            <div class="font-semibold">{{ $plan->title }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $plan->weeks }} weken • aangemaakt: {{ $plan->created_at->format('d-m-Y') }}
                            </div>
                            @if(!empty($plan->start_date))
                                <div class="text-[12px] text-gray-500">Start: {{ \Carbon\Carbon::parse($plan->start_date)->format('d-m-Y') }}</div>
                            @endif
                            @if($plan->is_final)
                                <div class="mt-1">{!! chip('Definitief','green') !!}</div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('coach.plans.show', $plan) }}" class="px-2 py-1 border rounded text-xs">Bekijken</a>
                            <a href="{{ route('coach.plans.edit', $plan) }}" class="px-2 py-1 border rounded text-xs">Bewerken</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Nog geen plannen voor deze klant.</p>
        @endif
    </section>

    {{-- TAB: Intake --}}
    <section x-show="tab==='intake'" x-transition class="p-6 bg-white rounded-3xl border">
        <h2 class="font-semibold mb-3">Intakegegevens</h2>

        @if($p)
            @php
                // helpers
                $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d-m-Y') : '—';
                $age     = $p->birthdate ? \Carbon\Carbon::parse($p->birthdate)->age : null;
                $gender  = match(($p->gender ?? '')) {
                    'm' => 'Man', 'f' => 'Vrouw', 'x' => 'X', default => '—'
                };

                // JSON velden veilig parsen (kunnen string of array zijn)
                $asArray = function ($v) {
                    if (is_array($v)) return $v;
                    if (is_string($v) && $v !== '') return json_decode($v, true) ?: [];
                    return [];
                };

                $injuries   = $asArray($p->injuries);
                $goals      = $asArray($p->goals);
                $frequency  = $asArray($p->frequency); // verwacht bv. sessions_per_week / minutes_per_session
                $facilities = $asArray($p->facilities);
                $materials  = $asArray($p->materials);
                $workHours  = $asArray($p->work_hours);

                // tests kunnen getallen of object zijn
                $test12     = $asArray($p->test_12min);
                $test5k     = $asArray($p->test_5k);

                // nette lijst-weergave
                $list = function ($arr) {
                    $arr = array_filter(is_array($arr) ? $arr : []);
                    return $arr ? implode(', ', array_map(fn($v) => is_array($v)? json_encode($v) : (string)$v, $arr)) : '—';
                };

                // frequency samenvatting
                $freqText = '—';
                if ($frequency) {
                    $spw = $frequency['sessions_per_week']   ?? null;
                    $mps = $frequency['minutes_per_session'] ?? null;
                    $pref= $frequency['prefer_days'] ?? null; if (is_string($pref)) $pref = json_decode($pref, true) ?: $pref;
                    $days= $pref ? (is_array($pref) ? implode(', ', $pref) : (string)$pref) : null;

                    $bits = [];
                    if ($spw) $bits[] = $spw.' sessies/wk';
                    if ($mps) $bits[] = $mps.' min/sessie';
                    if ($days) $bits[] = 'voorkeur: '.$days;
                    $freqText = $bits ? implode(' • ', $bits) : '—';
                }

                // tests samenvatting (probeer wat gangbare keys)
                $test12Text = '—';
                if ($test12) {
                    $d = $test12['distance_m'] ?? $test12['distance'] ?? null;
                    $p = $test12['pace'] ?? null;
                    $test12Text = $d ? ($d.' m'.($p ? ' • '.$p : '')) : (is_string($p) ? $p : '—');
                }
                $test5kText = '—';
                if ($test5k) {
                    $t = $test5k['time'] ?? $test5k['time_sec'] ?? $test5k['time_s'] ?? null;
                    $pace = $test5k['pace'] ?? null;
                    if (is_numeric($t)) {
                        $mins = floor($t/60); $secs = $t%60;
                        $t = sprintf('%d:%02d', $mins, $secs);
                    }
                    $test5kText = $t ? ($t.($pace ? ' • '.$pace : '')) : ($pace ?: '—');
                }
            @endphp

            {{-- Basis --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <div class="text-gray-500">Naam</div>
                    <div class="font-semibold">{{ $p->name ?? $client->user->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">E-mail</div>
                    <div class="font-semibold">{{ $p->email ?? $client->user->email ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Geboortedatum</div>
                    <div class="font-semibold">{{ $fmtDate($p->birthdate) }} {!! $age ? " <span class='text-gray-500'>({$age} jaar)</span>" : '' !!}</div>
                </div>
                <div>
                    <div class="text-gray-500">Geslacht</div>
                    <div class="font-semibold">{{ $gender }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Lengte</div>
                    <div class="font-semibold">{{ $p->height_cm ? $p->height_cm.' cm' : '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Gewicht (intake)</div>
                    <div class="font-semibold">{{ $p->weight_kg ? number_format($p->weight_kg,1,',','.') . ' kg' : '—' }}</div>
                </div>
                <div class="md:col-span-3">
                    <div class="text-gray-500">Adres</div>
                    <div class="font-semibold">{{ $p->address ?? '—' }}</div>
                </div>
            </div>

            {{-- Doelen & beperkingen --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6">
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">Doelen</div>
                    <div class="font-semibold">{{ $list($goals) }}</div>
                </div>
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">Blessures / Aandachtspunten</div>
                    <div class="font-semibold">{{ $list($injuries) }}</div>
                </div>
            </div>

            {{-- Trainingskader --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">Frequentie</div>
                    <div class="font-semibold">{{ $freqText }}</div>
                </div>
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">Beschikbare faciliteiten</div>
                    <div class="font-semibold">{{ $list($facilities) }}</div>
                </div>
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">Materiaal</div>
                    <div class="font-semibold">{{ $list($materials) }}</div>
                </div>
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">Werkuren / beschikbaarheid</div>
                    <div class="font-semibold">{{ $list($workHours) }}</div>
                </div>
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">Hartslag (rust/max)</div>
                    <div class="font-semibold">
                        @php
                            $hr = $p->heartrate;
                            if (is_string($hr)) $hr = json_decode($hr, true) ?: $hr;
                            $rest = is_array($hr) ? ($hr['resting'] ?? null) : null;
                            $max  = is_array($hr) ? ($hr['max']  ?? null) : null;
                        @endphp
                        {{ $rest ? $rest.' bpm' : '—' }} {{ $max ? ' / '.$max.' bpm' : '' }}
                    </div>
                </div>
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">Periode (weken)</div>
                    <div class="font-semibold">{{ $p->period_weeks ?? '—' }}</div>
                </div>
            </div>

            {{-- Tests --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6">
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">12-min test</div>
                    <div class="font-semibold">{{ $test12Text }}</div>
                </div>
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">5 km test</div>
                    <div class="font-semibold">{{ $test5kText }}</div>
                </div>
            </div>

            {{-- Coach voorkeur --}}
            <div class="grid grid-cols-1 gap-4 text-sm">
                <div class="p-4 border rounded-2xl bg-white">
                    <div class="text-gray-500 mb-1">Coach voorkeur</div>
                    <div class="font-semibold">{{ $p->coach_preference ?? '—' }}</div>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500">Geen intakeprofiel gevonden.</p>
        @endif
    </section>

    {{-- TAB: Gesprekken --}}
    <section x-show="tab==='threads'" x-transition class="p-6 bg-white rounded-3xl border">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold">Gesprekken</h2>
            <a href="{{ route('coach.threads.index', ['client' => $client->id]) }}" class="text-xs underline">Alle gesprekken</a>
        </div>

        @if($client->threads->count())
            <ul class="flex flex-col gap-2">
                @foreach($client->threads as $t)
                    <li class="p-4 border rounded-xl flex items-center justify-between">
                        <div>
                            <div class="font-semibold">{{ $t->subject ?? 'Gesprek' }}</div>
                            <div class="text-xs text-gray-500">Gestart: {{ $t->created_at->format('d-m-Y H:i') }}</div>
                        </div>
                        <a href="{{ route('coach.threads.show', $t) }}" class="px-2 py-1 border rounded text-xs">Openen</a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Geen gesprekken.</p>
        @endif
    </section>

    {{-- TAB: Metingen --}}
    <section x-show="tab==='weigh'" x-transition class="p-6 bg-white rounded-3xl border">
        <h2 class="font-semibold mb-3">Metingen / Weegmomenten</h2>
        @if($client->weighIns->count())
            <ul class="flex flex-col gap-2 text-sm">
                @foreach($client->weighIns as $w)
                    <li class="p-3 border rounded-xl flex items-center justify-between">
                        <div>
                            <div class="font-semibold">{{ number_format($w->weight_kg,1,',','.') }} kg</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($w->date)->format('d-m-Y') }}</div>
                        </div>
                        @if($w->notes)
                            <div class="text-xs text-gray-600">{{ $w->notes }}</div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Nog geen metingen geregistreerd.</p>
        @endif
    </section>

    {{-- TAB: Betalingen --}}
    <section x-show="tab==='payments'" x-transition class="p-6 bg-white rounded-3xl border">
        <h2 class="font-semibold mb-3">Betalingen</h2>
        @if($client->payments->count())
            <ul class="flex flex-col gap-2 text-sm">
                @foreach($client->payments as $pay)
                    <li class="p-3 border rounded-xl flex items-center justify-between">
                        <div>
                            <div class="font-semibold">
                                € {{ number_format($pay->amount, 2, ',', '.') }}
                                <span class="text-xs text-gray-500 ml-2">#{{ $pay->id }}</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $pay->created_at?->format('d-m-Y H:i') }} • {{ $pay->status ?? '—' }}
                            </div>
                        </div>
                        @if(!empty($pay->description))
                            <div class="text-xs text-gray-600">{{ $pay->description }}</div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Geen betalingen gevonden.</p>
        @endif
    </section>

    {{-- TAB: Abonnement --}}
    <section x-show="tab==='subs'" x-transition class="p-6 bg-white rounded-3xl border">
        <h2 class="font-semibold mb-3">Abonnement</h2>
        @if($client->subscriptions->count())
            <ul class="flex flex-col gap-2 text-sm">
                @foreach($client->subscriptions as $sub)
                    <li class="p-3 border rounded-xl flex items-center justify-between">
                        <div>
                            <div class="font-semibold">{{ ucfirst($sub->status) }}</div>
                            <div class="text-xs text-gray-500">
                                Start: {{ optional($sub->starts_at)->format('d-m-Y') ?? '—' }}
                                @if($sub->ends_at) • Einde: {{ $sub->ends_at->format('d-m-Y') }} @endif
                            </div>
                        </div>
                        @if(!is_null($sub->price_cents ?? null))
                            <div class="text-xs text-gray-600">
                                € {{ number_format(($sub->price_cents/100), 2, ',', '.') }}/{{ $sub->interval ?? 'mnd' }}
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Geen abonnementen gevonden.</p>
        @endif
    </section>
</div>
@endsection
