@extends('layouts.app')

@section('content')
@php
    // herbruikbare utility classes (zelfde look & feel als elders)
    $cardClass   = 'p-5 bg-white rounded-3xl border';
    $inputClass  = 'w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm';
    $selectClass = $inputClass;
    $taClass     = $inputClass;
    $btnPrimary  = 'px-6 py-3 bg-[#c8ab7a] hover:bg-[#a38b62] transition duration-300 text-white font-medium text-sm rounded';
    $btnGhost    = 'text-xs opacity-50 hover:opacity-100 transition duration-300 font-semibold';
@endphp

    <div
        x-data="intakeFlow({{ $intake->id }}, {{ json_encode($steps) }})"
        x-init="init()"
        class="max-w-xl mx-auto p-6"
    >
    <h1 class="text-2xl font-bold mb-2 flex items-center">
        <div class="flex">
            <div class="w-10 h-10 border-2 border-[#f9f6f1] rounded-full bg-black bg-cover bg-top relative bg-[url(https://cdn6.site-media.eu/images/640%2C1160x772%2B130%2B112/18694492/coachNicky-MjbAPBl6Pr1a23o9d6zbqA.webp)]"></div>
            <div class="-left-3 w-10 h-10 border-2 border-[#f9f6f1] rounded-full bg-black bg-cover bg-top relative bg-[url(https://cdn6.site-media.eu/images/576%2C1160x772%2B150%2B121/18694504/coachEline-DVsTZnUZ-eQ_EWm1zNyfww.webp)]"></div>
            <div class="-left-6 w-10 h-10 border-2 border-[#f9f6f1] rounded-full bg-black bg-cover bg-top relative bg-[url(https://cdn6.site-media.eu/images/576%2C1160x772%2B134%2B41/18694509/coachRoy-LCXiB9ufGNk2uXEnykijBA.webp)]"></div>
        </div>
        <div class="bg-white h-9 px-4 rounded-xl flex items-center relative -ml-2">
            <div class="w-4 h-4 rotate-[45deg] rounded-sm absolute -left-1 bg-white"></div>
            <p class="italic text-[10px] leading-tighter font-semibold">"Leuk je te zien! Klaar om te knallen? 🔥"</p>
        </div>
    </h1>
    <h2 class="text-xl font-bold mb-2">
        Intakeformulier
    </h2>
    <p class="text-sm font-medium text-black/60 mb-8">Vul je gegevens stap voor stap in. Je kunt velden overslaan en later aanvullen.</p>

    <template x-if="!complete">
        <div class="space-y-3">
            {{-- voortgang --}}
            <div class="flex items-center justify-between text-xs text-black/50 font-semibold">
                <div>
                    Stap <span x-text="stepIndex+1"></span>/<span x-text="steps.length"></span>
                </div>
                <div class="h-2 bg-gray-200 rounded-full w-48 overflow-hidden">
                    <div class="h-2 bg-[#c8ab7a] transition-all duration-300"
                         :style="`width:${Math.round((stepIndex+1)/steps.length*100)}%`"></div>
                </div>
            </div>

            {{-- card --}}
            <div class="{{ $cardClass }}" x-show="currentStep()">
                <div class="mb-1 font-semibold text-[15px] flex items-center gap-1">
                    <span x-html="labelFor(currentStep())"></span>
                    <span x-show="!isOptional(currentStep())" class="text-black">*</span>
                </div>

                {{-- Tekst --}}
                <template x-if="inputType(currentStep()) === 'text'">
                    <input x-model="answer" type="text" class="{{ $inputClass }}" :placeholder="placeholderFor(currentStep())">
                </template>

                {{-- Email --}}
                <template x-if="inputType(currentStep()) === 'email'">
                    <input x-model="answer" type="email" class="{{ $inputClass }}" placeholder="jij@example.com">
                </template>

                {{-- Datum --}}
                <template x-if="inputType(currentStep()) === 'date'">
                    <input x-model="answer" type="date" class="{{ $inputClass }}">
                </template>

                {{-- Nummer --}}
                <template x-if="inputType(currentStep()) === 'number'">
                    <input x-model.number="answer" type="number" class="{{ $inputClass }}" :placeholder="placeholderFor(currentStep())">
                </template>

                {{-- Select (gender / period_weeks) --}}
                <template x-if="inputType(currentStep()) === 'select'">
                    <select x-model="answer" class="{{ $selectClass }}">
                        <template x-for="opt in optionsFor(currentStep())" :key="opt.value">
                            <option :value="opt.value" x-text="opt.label"></option>
                        </template>
                    </select>
                </template>

                {{-- Tags (goals/injuries) --}}
                <template x-if="inputType(currentStep()) === 'tags'">
                    <div>
                        <input x-model="tagsInput" type="text" class="{{ $inputClass }}" :placeholder="placeholderFor(currentStep())">
                        <p class="text-xs text-black/50 mt-1.5">Meerdere items? Scheid met komma’s (bijv. <em>5k sneller, techniek</em>).</p>
                    </div>
                </template>

                {{-- Frequentie --}}
                <template x-if="currentStep()==='frequency'">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 mb-1 block">Sessies per week</label>
                            <input x-model.number="freq.sessions_per_week" type="number" min="1" class="{{ $inputClass }}" placeholder="3">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 mb-1 block">Minuten per sessie</label>
                            <input x-model.number="freq.minutes_per_session" type="number" min="10" class="{{ $inputClass }}" placeholder="60">
                        </div>
                    </div>
                </template>

                {{-- Hartslag --}}
                <template x-if="currentStep()==='heartrate'">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 mb-1 block">Rust (bpm)</label>
                            <input x-model.number="hr.resting" type="number" min="20" class="{{ $inputClass }}" placeholder="52">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 mb-1 block">Max (bpm)</label>
                            <input x-model.number="hr.max" type="number" min="80" class="{{ $inputClass }}" placeholder="187">
                        </div>
                    </div>
                </template>

                {{-- 12-min test --}}
                <template x-if="currentStep()==='test_12min'">
                    <div>
                        <label class="text-xs text-gray-600 mb-1 block">Meters in 12 min</label>
                        <input x-model.number="t12.meters" type="number" min="0" class="{{ $inputClass }}" placeholder="2800">
                    </div>
                </template>

                {{-- 5k test --}}
                <template x-if="currentStep()==='test_5k'">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 mb-1 block">Minuten</label>
                            <input x-model.number="t5k.minutes" type="number" min="0" class="{{ $inputClass }}" placeholder="22">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 mb-1 block">Seconden</label>
                            <input x-model.number="t5k.seconds" type="number" min="0" max="59" class="{{ $inputClass }}" placeholder="30">
                        </div>
                    </div>
                </template>

                {{-- Coach voorkeur (cards) --}}
                <template x-if="inputType(currentStep()) === 'coach_preference'">
                    <div class="grid grid-cols-3 gap-3">
                        <!-- Eline -->
                        <button type="button"
                                @click="answer='eline'"
                                class="group relative overflow-hidden rounded-2xl border bg-white p-2 text-left transition"
                                :class="answer==='eline' ? 'border-[#c8ab7a] ring-2 ring-[#c8ab7a]/30' : 'border-gray-200 hover:border-gray-300'">
                            <div class="w-full">
                                <img src="/assets/eline.webp" alt="Eline">
                            </div>
                            <div class="mt-4 px-2 pb-2 flex items-center justify-between">
                                <span class="font-semibold">Eline</span>
                                <i class="fa-solid fa-circle-check"
                                :class="answer==='eline' ? 'text-[#c8ab7a]' : 'text-gray-300'"></i>
                            </div>
                        </button>

                        <!-- Nicky -->
                        <button type="button"
                                @click="answer='nicky'"
                                class="group relative overflow-hidden rounded-2xl border bg-white p-2 text-left transition"
                                :class="answer==='nicky' ? 'border-[#c8ab7a] ring-2 ring-[#c8ab7a]/30' : 'border-gray-200 hover:border-gray-300'">
                            <div class="w-full">
                                <img src="/assets/nicky.webp" alt="Eline">
                            </div>
                            <div class="mt-4 px-2 pb-2 flex items-center justify-between">
                                <span class="font-semibold">Nicky</span>
                                <i class="fa-solid fa-circle-check"
                                :class="answer==='nicky' ? 'text-[#c8ab7a]' : 'text-gray-300'"></i>
                            </div>
                        </button>

                        <!-- Roy -->
                        <button type="button"
                                @click="answer='roy'"
                                class="group relative overflow-hidden rounded-2xl border bg-white p-2 text-left transition"
                                :class="answer==='roy' ? 'border-[#c8ab7a] ring-2 ring-[#c8ab7a]/30' : 'border-gray-200 hover:border-gray-300'">
                            <div class="w-full">
                                <img src="/assets/roy.webp" alt="Eline">
                            </div>
                            <div class="mt-4 px-2 pb-2 flex items-center justify-between">
                                <span class="font-semibold">Roy</span>
                                <i class="fa-solid fa-circle-check"
                                :class="answer==='roy' ? 'text-[#c8ab7a]' : 'text-gray-300'"></i>
                            </div>
                        </button>

                        <!-- Geen voorkeur -->
                        <button type="button"
                                @click="answer='none'"
                                class="group col-span-3 relative overflow-hidden rounded-2xl border bg-white p-2 text-left transition"
                                :class="answer==='none' ? 'border-[#c8ab7a] ring-2 ring-[#c8ab7a]/30' : 'border-gray-200 hover:border-gray-300'">
                            <div class="h-28 w-full rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 grid place-items-center">
                                <i class="fa-regular fa-user text-3xl text-gray-400"></i>
                            </div>
                            <div class="mt-2 px-2 pb-2 flex items-center justify-between">
                                <span class="font-semibold">Geen voorkeur</span>
                                <i class="fa-solid fa-circle-check"
                                :class="answer==='none' ? 'text-[#c8ab7a]' : 'text-gray-300'"></i>
                            </div>
                            <p class="text-xs text-black/60">Wij matchen je met de beste coach</p>
                        </button>
                    </div>
                </template>

                {{-- Lange tekstvelden --}}
                <template x-if="inputType(currentStep()) === 'textarea'">
                    <textarea x-model="answer" rows="4" class="{{ $taClass }}" :placeholder="placeholderFor(currentStep())"></textarea>
                </template>

                {{-- actions --}}
                <div class="mt-6 flex items-center gap-3">
                    <button @click="submitStep" :disabled="loading" class="{{ $btnPrimary }}">
                        <span x-show="!loading" x-text="isLast() ? 'Afronden' : 'Volgende'"></span>
                        <span x-show="loading">Opslaan…</span>
                    </button>

                    {{-- Alleen tonen als de stap optioneel is --}}
                    <button
                        @click="skipStep"
                        x-show="isOptional(currentStep()) && !isLast()"
                        class="{{ $btnGhost }}">
                        Overslaan
                    </button>
                </div>

                <p x-show="error" x-text="error" class="mt-3 text-sm text-red-600"></p>
            </div>
        </div>
    </template>

    @php
        // Bepaal gekozen looptijd uit intake → val terug op profiel → default 12
        $chosenWeeks = (int)($intake->payload['period_weeks'] ?? ($intake->client->profile->period_weeks ?? 12));
        $chosenWeeks = in_array($chosenWeeks, [12, 24]) ? $chosenWeeks : 12;

        // Prijzen per pakket
        $prices = [12 => 299, 24 => 499];
        $price  = $prices[$chosenWeeks] ?? 299;
        $perWeek = $price / $chosenWeeks;
    @endphp
    <template x-if="complete">
        <div class="p-6 rounded-3xl border bg-white">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 text-[12px] px-2 py-0.5 rounded-full border bg-green-50 text-green-700 border-green-200 font-semibold">
                        <i class="fa-solid fa-check-circle"></i>
                        Intake voltooid
                    </div>
                    <h2 class="mt-2 text-xl font-semibold">Jouw pakket</h2>
                    <p class="text-sm text-black/60">We hebben je voorkeuren opgeslagen. Hieronder staat je traject.</p>
                </div>
            </div>

            {{-- Pakketkaart --}}
            <div class="mt-4 grid grid-cols-1 gap-4 items-stretch">
                {{-- Linker info --}}
                <div class="p-5 rounded-2xl border bg-gray-50">
                    <div class="text-[12px] text-gray-600 mb-1">Traject</div>
                    <div class="text-2xl font-bold text-[#c8ab7a]">{{ $chosenWeeks }} weken coaching</div>

                    <ul class="mt-3 space-y-2 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-check mt-0.5 text-[#c8ab7a]"></i>
                            Persoonlijk trainingsschema afgestemd op jouw doelen
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-check mt-0.5 text-[#c8ab7a]"></i>
                            Wekelijkse bijsturing op basis van voortgang en feedback
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-check mt-0.5 text-[#c8ab7a]"></i>
                            Chat met je coach voor vragen & techniek-tips
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-check mt-0.5 text-[#c8ab7a]"></i>
                            Weegmomenten & metingen overzichtelijk in je dashboard
                        </li>
                    </ul>
                </div>

                {{-- Rechter prijs/CTA --}}
                <div class="p-5 rounded-2xl border bg-white flex flex-col justify-between">
                    <div>
                        <div class="text-[12px] text-gray-600 mb-1">Totaal</div>
                        <div class="text-3xl font-extrabold text-[#c8ab7a]">€ {{ number_format($price, 2, ',', '.') }}</div>
                        <div class="text-xs text-gray-500">≈ € {{ number_format($perWeek, 2, ',', '.') }} per week</div>
                    </div>

                    <form class="mt-4" method="POST" action="{{ route('checkout.create') }}">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $intake->client_id }}">
                        <input type="hidden" name="period_weeks" value="{{ $chosenWeeks }}"> {{-- automatisch gekozen --}}
                        <button class="w-full px-6 py-3 bg-[#c8ab7a] hover:bg-[#a38b62] transition duration-300 text-white font-medium text-sm rounded">
                            Ga door naar afrekenen
                        </button>
                    </form>

                    <p class="mt-2 text-[12px] text-gray-500">
                        Vragen over het pakket? Neem contact met ons op.
                    </p>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function intakeFlow(intakeId, steps){
    return {
        // props
        intakeId, steps,

        // state
        stepIndex: 0,
        complete: false,
        loading:false,
        error:'',
        optionalSteps: new Set([
            'address','background','facilities','materials','work_hours',
            'injuries','test_12min','test_5k','coach_preference'
        ]),

        // models (werden al gebruikt door je UI)
        answer: '',
        tagsInput: '',
        freq: { sessions_per_week: null, minutes_per_session: null },
        hr:   { resting: null, max: null },
        t12:  { meters: null },
        t5k:  { minutes: null, seconds: null },

        // ---------- LocalStorage helpers ----------
        storageKey(){ return `intake:${this.intakeId}`; },

        saveDraft(){
            const draft = {
                stepIndex: this.stepIndex,
                answer: this.answer,
                tagsInput: this.tagsInput,
                freq: this.freq,
                hr: this.hr,
                t12: this.t12,
                t5k: this.t5k,
                ts: Date.now(),
            };
            try { localStorage.setItem(this.storageKey(), JSON.stringify(draft)); } catch(e){}
        },

        loadDraft(){
            try{
                const raw = localStorage.getItem(this.storageKey());
                if (!raw) return false;
                const d = JSON.parse(raw);
                if (!d || typeof d !== 'object') return false;

                // herstel state
                this.stepIndex = Number.isInteger(d.stepIndex) ? d.stepIndex : 0;
                this.answer    = d.answer ?? '';
                this.tagsInput = d.tagsInput ?? '';
                this.freq      = d.freq ?? { sessions_per_week: null, minutes_per_session: null };
                this.hr        = d.hr   ?? { resting: null, max: null };
                this.t12       = d.t12  ?? { meters: null };
                this.t5k       = d.t5k  ?? { minutes: null, seconds: null };
                return true;
            } catch(e){ return false; }
        },

        clearDraft(){
            try { localStorage.removeItem(this.storageKey()); } catch(e){}
        },

        // ---------- Lifecycle ----------
        init(){
            // 1) Probeer draft te laden
            const hadDraft = this.loadDraft();

            // 2) Safety: clamp stepIndex
            if (!Array.isArray(this.steps) || this.steps.length === 0){
                this.complete = true;
            } else {
                if (!Number.isInteger(this.stepIndex) || this.stepIndex < 0) this.stepIndex = 0;
                if (this.stepIndex > this.steps.length - 1) this.stepIndex = 0;
            }

            // 3) Defaults voor huidige stap
            this.watchStep();

            // 4) Autosave bij relevante wijzigingen
            this.$watch('stepIndex', () => this.saveDraft());
            this.$watch('answer',    () => this.saveDraft());
            this.$watch('tagsInput', () => this.saveDraft());
            this.$watch(() => JSON.stringify(this.freq), () => this.saveDraft());
            this.$watch(() => JSON.stringify(this.hr),   () => this.saveDraft());
            this.$watch(() => JSON.stringify(this.t12),  () => this.saveDraft());
            this.$watch(() => JSON.stringify(this.t5k),  () => this.saveDraft());

            // 5) Extra safety
            window.addEventListener('beforeunload', () => this.saveDraft());
        },

        // ---------- Step helpers ----------
        currentStep(){
            if (!Array.isArray(this.steps) || this.steps.length === 0) return null;
            if (this.stepIndex < 0 || this.stepIndex > this.steps.length - 1) return null;
            return this.steps[this.stepIndex] ?? null;
        },
        isLast(){ return this.stepIndex >= this.steps.length - 1; },
        isOptional(step){ return this.optionalSteps.has(step); },

        resetFields(){
            // reset alleen field-state (niet de stepIndex)
            this.answer=''; this.tagsInput='';
            this.freq={sessions_per_week:null, minutes_per_session:null};
            this.hr={resting:null, max:null};
            this.t12={meters:null};
            this.t5k={minutes:null, seconds:null};
        },

        watchStep(){
            const s = this.currentStep();
            if (s === 'period_weeks' && (this.answer === null || this.answer === '' || this.answer === undefined)) {
                this.answer = 12;
            }
            if (s === 'frequency' && (!this.freq || typeof this.freq !== 'object')) {
                this.freq = { sessions_per_week: null, minutes_per_session: null };
            }
            if (s === 'coach_preference' && !this.answer) {
                this.answer = 'none'; // standaard 'Geen voorkeur'
            }
        },

        // ---------- UI meta ----------
        inputType(key){
            if (!key) return 'text';
            if (key === 'email') return 'email';
            if (key === 'birthdate') return 'date';
            if (['height_cm','weight_kg'].includes(key)) return 'number';
            if (['gender','period_weeks'].includes(key)) return 'select';
            if (['background','facilities','materials','work_hours'].includes(key)) return 'textarea';
            if (['goals','injuries'].includes(key)) return 'tags';
            if (['frequency','heartrate','test_12min','test_5k'].includes(key)) return key;
            if (key === 'coach_preference') return 'coach_preference';
            return 'text';
        },

        labelFor(key){
            if (!key) return '';
            const labels = {
                name:'Wat is je naam?', email:'Wat is je e-mail?',
                birthdate:'Wat is je geboortedatum?', address:'Wat is je adres?<span class="opacity-50 text-[10px] ml-2">OPTIONEEL</span>', gender:'Wat is je geslacht?',
                height_cm:'Wat is je lengte in centimeter?', weight_kg:'Wat is je huidige gewicht in kilogram?',
                injuries:'Heb je momenteel blessures?<span class="opacity-50 text-[10px] ml-2">OPTIONEEL</span>', goals:'Trainingsdoelen (min. 1)?',
                period_weeks:'Kies je trajectduur', frequency:'Hoevaak per week en hoelang wil je trainen? (sessies/duur)',
                background:'Wat is je sportachtegrond?<span class="opacity-50 text-[10px] ml-2">OPTIONEEL</span>', facilities:'Welke faciliteiten heb je in de buurt?<span class="opacity-50 text-[10px] ml-2">OPTIONEEL</span>',
                materials:'Welke materialen heb je tot je beschikking?<span class="opacity-50 text-[10px] ml-2">OPTIONEEL</span>', work_hours:'Werktijden (optioneel)?',
                heartrate:'Heb je hartslag informatie?<span class="opacity-50 text-[10px] ml-2">OPTIONEEL</span>', test_12min:'Heb je je 12-min test-run resultaten?<span class="opacity-50 text-[10px] ml-2">OPTIONEEL</span>',
                test_5k:'Heb je je 5km test-run resultaten?<span class="opacity-50 text-[10px] ml-2">OPTIONEEL</span>', coach_preference:'Heb je een voorkeur qua coach?<span class="opacity-50 text-[10px] ml-2">OPTIONEEL</span>'
            };
            return labels[key] || key.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
        },

        placeholderFor(key){
            const ph = {
                email:'jij@example.com', height_cm:'Bijv. 176', weight_kg:'Bijv. 72.5',
                background:'Vertel iets over je sportachtergrond', facilities:'Bijv. sportschool / thuis',
                materials:'Bijv. sled, kettlebell', work_hours:'Bijv. ma–vr 9–17',
                goals:'Bijv. 5k sneller, techniek', injuries:'Bijv. knie, schouder'
            };
            return ph[key] || '';
        },

        optionsFor(key){
            if (key === 'gender') return [
                {value:'',label:'-'},{value:'m',label:'Man'},{value:'f',label:'Vrouw'}
            ];
            if (key === 'period_weeks') return [
                {value:12,label:'12 weken'},{value:24,label:'24 weken'}
            ];
            return [];
        },

        buildValue(step){
            if (['goals','injuries'].includes(step)) {
                return this.tagsInput.split(',').map(s => s.trim()).filter(Boolean);
            }
            if (step === 'period_weeks') {
                const v = parseInt(this.answer, 10);
                return (v === 12 || v === 24) ? v : 12;
            }
            if (step === 'frequency') {
                const s = Number(this.freq.sessions_per_week || 0);
                const m = Number(this.freq.minutes_per_session || 0);
                return (s && m) ? { sessions_per_week: s, minutes_per_session: m } : null;
            }
            if (step === 'heartrate') {
                const r = Number(this.hr.resting || 0);
                const x = Number(this.hr.max || 0);
                return (r || x) ? { resting: r || null, max: x || null } : null;
            }
            if (step === 'test_12min') {
                const meters = Number(this.t12.meters || 0);
                return meters ? { meters } : null;
            }
            if (step === 'test_5k') {
                const minutes = Number(this.t5k.minutes || 0);
                const seconds = Number(this.t5k.seconds || 0);
                return (minutes || seconds) ? { minutes: minutes || 0, seconds: seconds || 0 } : null;
            }
            return this.answer ?? null;
        },

        validateCurrentStep(){
            const step = this.currentStep();
            if (this.isOptional(step)) return true;

            const val = this.buildValue(step);
            const isEmpty = (v) =>
                v === null || v === undefined ||
                (typeof v === 'string' && v.trim() === '') ||
                (Array.isArray(v) && v.length === 0) ||
                (typeof v === 'object' && !Array.isArray(v) && Object.keys(v).length === 0);

            if (isEmpty(val)){ this.error = 'Dit veld is verplicht.'; return false; }
            if (step === 'email'){
                const re = /^\S+@\S+\.\S+$/;
                if (!re.test(val)){ this.error = 'Vul een geldig e-mailadres in.'; return false; }
            }
            if (step === 'height_cm' || step === 'weight_kg'){
                if (Number(val) <= 0){ this.error = 'Vul een geldige waarde in.'; return false; }
            }
            if (step === 'goals'){
                if (!Array.isArray(val) || val.length === 0){ this.error = 'Voeg ten minste één doel toe.'; return false; }
            }
            if (step === 'period_weeks'){
                if (!(val === 12 || val === 24)){ this.error = 'Kies 12 of 24 weken.'; return false; }
            }
            if (step === 'frequency'){
                if (!val || !val.sessions_per_week || !val.minutes_per_session){
                    this.error = 'Vul beide frequentievelden in.'; return false;
                }
            }
            return true;
        },

        // ---------- Actions ----------
        async submitStep(){
            this.error='';
            if (!this.validateCurrentStep()) return;

            this.loading=true;
            const step  = this.currentStep();
            const value = this.buildValue(step);

            try{
                const res = await fetch('{{ route('intake.step') }}', {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({intake_id:this.intakeId, step, value})
                });

                if (!res.ok){
                    const raw = await res.text();
                    console.error('intake.step response:', raw);
                    try { this.error = (JSON.parse(raw).message) || 'Er ging iets mis bij opslaan.'; }
                    catch { this.error = 'Serverfout bij opslaan (details in console).'; }
                    this.loading=false;
                    return;
                }

                const data = await res.json();

                // altijd draft updaten
                this.saveDraft();

                if (data.complete){
                    this.complete = true;
                    this.clearDraft(); // klaar → draft weg
                    return;
                }

                if (this.isLast()){
                    this.complete = true;
                    this.clearDraft();
                    return;
                }

                this.stepIndex++;
                this.resetFields();
                this.watchStep();
                this.saveDraft();

            } catch (e){
                this.error='Netwerkfout. Probeer opnieuw.';
            } finally {
                this.loading=false;
            }
        },

        skipStep(){
            this.error='';
            if (!this.isOptional(this.currentStep())) return;

            if (this.isLast()){
                this.complete = true;
                this.clearDraft();
                return;
            }
            this.stepIndex++;
            this.resetFields();
            this.watchStep();
            this.saveDraft();
        }
    }
}
</script>
@endpush
@endsection
