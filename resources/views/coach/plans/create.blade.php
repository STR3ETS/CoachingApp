@extends('layouts.app')
@section('title','Plan aanmaken')

@section('content')
<h1 class="text-2xl font-semibold mb-6">
  Plan aanmaken voor {{ $client->user->name ?? 'Client #'.$client->id }}
</h1>

<form method="POST" action="{{ route('coach.plans.store') }}"
      x-data="planGridBuilder({{ (int)($client->profile->period_weeks ?? 12) }})"
      x-init="init()" class="max-w-5xl">
    @csrf
    <input type="hidden" name="client_id" value="{{ $client->id }}">

    {{-- Meta --}}
    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Titel</label>
            <input x-model="title" name="title" class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm" placeholder="Hyrox schema">
        </div>
        <div>
            <label class="block text-sm mb-1">Aantal weken</label>
            <input x-model.number="weeks" type="number" name="weeks" min="1" max="52"
                   class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm">
            <p class="text-xs text-gray-500 mt-1">Wijzig het aantal weken om secties toe te voegen/verwijderen.</p>
        </div>
    </div>

    {{-- Alle weken onder elkaar --}}
    <div class="space-y-8">
        <template x-for="w in weeksArray()" :key="w">
            <section class="p-5 bg-white rounded-3xl border">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Week <span x-text="w"></span></h2>
                    <div class="flex items-center gap-6">
                        <button type="button" class="text-xs opacity-50 hover:opacity-100 transition duration-300 font-semibold"
                                @click="generateWeek(w)" x-bind:disabled="loading[w]">
                            <span x-show="!loading[w]">Genereer week</span>
                            <span x-show="loading[w]">Genereert…</span>
                        </button>
                        <button type="button" class="text-xs opacity-50 hover:opacity-100 transition duration-300 font-semibold"
                                @click="clearWeek(w)">Leeg week</button>
                    </div>
                </div>

                {{-- Focus --}}
                <div class="mt-3">
                    <label class="block text-sm mb-1">Focus</label>
                    <input class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm"
                           :name="`focus_week_${w}`"
                           x-model="plan[`week_${w}`].focus"
                           placeholder="Bijv. Basiskracht + aerobe duur">
                </div>

                {{-- Tabel: dag / oefening / sets / reps / rpe --}}
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="block text-sm">Sessies</h3>
                        <button type="button" class="text-xs opacity-50 hover:opacity-100 transition duration-300 font-semibold" @click="addRow(w)">Extra rij toevoegen</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border">
                            <thead class="bg-gray-50">
                                <tr class="border-b">
                                    <th class="text-left p-2 w-36">Dag</th>
                                    <th class="text-left p-2">Oefening</th>
                                    <th class="text-left p-2 w-24">Sets</th>
                                    <th class="text-left p-2 w-28">Reps</th>
                                    <th class="text-left p-2 w-24">RPE</th>
                                    <th class="text-left p-2 w-40">Notitie</th>
                                    <th class="text-left p-2 w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="flatRows(w).length === 0">
                                    <tr>
                                        <td colspan="7" class="p-3 text-gray-500">Nog geen rijen.</td>
                                    </tr>
                                </template>

                                <template x-for="(row, idx) in flatRows(w)" :key="idx">
                                    <tr class="border-t">
                                        <td class="p-2">
                                        <select class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm"
                                                x-model="row.day"
                                                @change="saveFlatRow(w, idx, row)">
                                            <option :value="''" disabled :selected="!row.day">Kies dag</option>
                                            <template x-for="d in days" :key="d">
                                                <option :value="d" :selected="row.day === d" x-text="d"></option>
                                            </template>
                                        </select>
                                        </td>
                                        <td class="p-2">
                                            <input class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm"
                                                   placeholder="Bijv. Sled push"
                                                   x-model="row.name"
                                                   @input="saveFlatRow(w, idx, row)">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm"
                                                   x-model.number="row.sets"
                                                   @input="saveFlatRow(w, idx, row)">
                                        </td>
                                        <td class="p-2">
                                            <input class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm"
                                                   placeholder="8-10"
                                                   x-model="row.reps"
                                                   @input="saveFlatRow(w, idx, row)">
                                        </td>
                                        <td class="p-2">
                                            <input class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm"
                                                   placeholder="7"
                                                   x-model="row.rpe"
                                                   @input="saveFlatRow(w, idx, row)">
                                        </td>
                                        <td class="p-2">
                                            <input class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm"
                                                placeholder="bv. tempo, cues"
                                                x-model="row.notes"
                                                @input="saveFlatRow(w, idx, row)">
                                        </td>
                                        <td class="p-2">
                                            <button type="button" class="px-2 py-1"
                                                    @click="removeFlatRow(w, idx)">
                                                  <i class="fa-solid fa-x fa-sm opacity-50"></i></button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <!-- Sessienotities per dag (onder de tabel) -->
                        <div class="mt-4 space-y-3">
                            <template
                                x-for="d in Array.from(new Set((plan['week_'+w]?.sessions || []).map(s => s.day).filter(Boolean)))"
                                :key="d"
                            >
                                <div class="p-3 border rounded">
                                <label class="block text-xs text-gray-600 mb-1">
                                    Notitie voor sessie: <span class="font-semibold" x-text="d"></span>
                                </label>
                                <textarea
                                    class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-sm"
                                    rows="2"
                                    x-model="sessionNotes[w][d]"
                                    @input="setSessionNote(w, d, sessionNotes[w][d])"
                                    placeholder="bv. focus van de dag, intensiteit, cues voor cliënt"
                                ></textarea>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </div>

    {{-- Hidden JSON output --}}
    <input type="hidden" name="plan_json" :value="serializedPlan()">

    <div class="mt-6 flex gap-3">
        <button class="px-6 py-3 bg-[#c8ab7a] hover:bg-[#a38b62] transition duration-300 text-white font-medium text-sm rounded">Opslaan</button>
        <a href="{{ route('coach.plans.index') }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 transition duration-300 text-gray-500 font-medium text-sm rounded">Annuleren</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
function planGridBuilder(defaultWeeks){
  return {
    title: '',
    weeks: defaultWeeks || 12,
    days: ['Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag','Zondag'],

    // plan: { week_1: { focus:'', sessions:[{ day:'Maandag', note:'', exercises:[{name,sets,reps,rpe,notes}] }] }, ... }
    plan: {},
    loading: {}, // per week loading state
    error: '',

    // Sessienotities per week/dag
    sessionNotes: {}, // { [weekNumber]: { [dayName]: 'text' } }

    getSessionNote(w, day){
      const d = (day || '').trim(); if (!d) return '';
      this.sessionNotes[w] ??= {};
      return this.sessionNotes[w][d] ?? '';
    },
    setSessionNote(w, day, val){
      const d = (day || '').trim(); if (!d) return;
      this.sessionNotes[w] ??= {};
      this.sessionNotes[w][d] = val || '';
    },

    init(){
      for (let i=1;i<=this.weeks;i++) this.ensureWeek(i);
    },
    weeksArray(){ return Array.from({length: this.weeks}, (_,i)=>i+1); },

    ensureWeek(w){
      const key = 'week_'+w;
      if (!this.plan[key]) this.plan[key] = { focus: '', sessions: [] };
      if (!Array.isArray(this.plan[key].sessions)) this.plan[key].sessions = [];
      if (!this.plan[key].sessions.length) {
        this.plan[key].sessions.push({ day:'', note:'', exercises: [] });
      }
      this.sessionNotes[w] ??= {};
    },
    clearWeek(w){
      this.plan['week_'+w] = { focus:'', sessions: [{day:'', note:'', exercises:[]}] };
      this.sessionNotes[w] = {};
    },

    // Platte rijen voor de grid
    flatRows(w){
      this.ensureWeek(w);
      const key = 'week_'+w;
      const rows = [];
      this.plan[key].sessions.forEach((s) => {
        const ex = Array.isArray(s.exercises) ? s.exercises : [];
        if (!ex.length){
          rows.push({ day: s.day, name:'', sets:null, reps:'', rpe:'', notes:'' });
          return;
        }
        ex.forEach(e => {
          rows.push({
            day:  s.day,
            name: e.name || '',
            sets: e.sets ?? null,
            reps: e.reps || '',
            rpe:  e.rpe  || '',
            notes: e.notes || ''
          });
        });
      });
      return rows;
    },

    addRow(w){
      const key = 'week_'+w;
      this.ensureWeek(w);
      let session = this.plan[key].sessions[0];
      if (!session) {
        session = { day:'', note:'', exercises:[] };
        this.plan[key].sessions.push(session);
      }
      session.exercises.push({ name:'', sets:null, reps:'', rpe:'', notes:'' });
    },

    // Rebuild sessies uit platte rijen (incl. sessienotities)
    saveFlatRow(w, idx, row){
      const key = 'week_'+w;
      const rows = this.flatRows(w);
      rows[idx] = row;

      const byDay = {};
      rows.forEach(r => {
        const day = (r.day || '').trim();
        if (!day) return;
        if (!byDay[day]) byDay[day] = [];
        if (r.name || r.sets || r.reps || r.rpe || r.notes) {
          byDay[day].push({
            name: r.name || '',
            sets: Number(r.sets) || null,
            reps: r.reps || '',
            rpe:  r.rpe  || '',
            notes: r.notes || ''
          });
        }
      });

      const sessions = Object.keys(byDay).length
        ? Object.keys(byDay).map(day => ({
            day,
            note: this.getSessionNote(w, day), // ← sessienotitie toevoegen
            exercises: byDay[day]
          }))
        : [];

      this.plan[key].sessions = sessions;
    },

    removeFlatRow(w, idx){
      const key = 'week_'+w;
      const rows = this.flatRows(w);
      rows.splice(idx,1);

      const byDay = {};
      rows.forEach(r => {
        const day = (r.day || '').trim();
        if (!day) return;
        if (!byDay[day]) byDay[day] = [];
        if (r.name || r.sets || r.reps || r.rpe || r.notes) {
          byDay[day].push({
            name: r.name || '',
            sets: Number(r.sets) || null,
            reps: r.reps || '',
            rpe:  r.rpe  || '',
            notes: r.notes || ''
          });
        }
      });

      const sessions = Object.keys(byDay).length
        ? Object.keys(byDay).map(day => ({
            day,
            note: this.getSessionNote(w, day),
            exercises: byDay[day]
          }))
        : [];

      this.plan[key].sessions = sessions;
    },

    async generateWeek(w){
      this.ensureWeek(w);
      this.loading[w] = true;

      try{
        const res = await fetch("{{ route('coach.plans.aiWeek', $client) }}", {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
          },
          body: JSON.stringify({ week: w })
        });

        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch {
          console.error('Server response was not JSON:', text);
          throw new Error('Kon week niet genereren (geen JSON). Ben je ingelogd en heb je coach-rechten?');
        }

        if (!res.ok || !data.ok) {
          throw new Error(data.message || `Kon week niet genereren (status ${res.status}).`);
        }

        // Normaliseer Engelstalige dagcodes → NL
        const map = {
          mon:'Maandag', monday:'Maandag', Mon:'Maandag',
          tue:'Dinsdag', tuesday:'Dinsdag', Tue:'Dinsdag',
          wed:'Woensdag', wednesday:'Woensdag', Wed:'Woensdag',
          thu:'Donderdag', thursday:'Donderdag', Thu:'Donderdag',
          fri:'Vrijdag', friday:'Vrijdag', Fri:'Vrijdag',
          sat:'Zaterdag', saturday:'Zaterdag', Sat:'Zaterdag',
          sun:'Zondag', sunday:'Zondag', Sun:'Zondag',
        };

        if (data?.data?.sessions?.length) {
          data.data.sessions = data.data.sessions.map(s => {
            const raw = (s.day || '').toString().trim();
            const key = raw.toLowerCase();
            const day = map[key] ?? raw;

            // sessienotitie in local state zetten
            if (s.note) this.setSessionNote(w, day, s.note);

            return {
              day,
              note: s.note || '', // bewaar ook in de sessie zelf
              exercises: (s.exercises || []).map(e => ({
                name: e.name || '',
                sets: Number(e.sets) || null,
                reps: e.reps || '',
                rpe:  e.rpe  || '',
                notes: e.notes || ''
              }))
            };
          });
        }

        this.plan['week_'+w] = {
          focus: data.data.focus || '',
          sessions: Array.isArray(data.data.sessions) ? data.data.sessions : []
        };

        if (data.source === 'local_fallback') {
          alert('AI niet beschikbaar; lokaal concept gebruikt.');
        }
      } catch (e){
        alert(e.message || 'Fout bij genereren.');
      } finally {
        this.loading[w] = false;
      }
    },

    // JSON dat je naar de server stuurt
    serializedPlan(){
      const out = {};
      for (let i=1;i<=this.weeks;i++){
        const k = 'week_'+i;
        this.ensureWeek(i);
        const v = this.plan[k];

        // Zorg dat elke sessie zijn actuele sessienote heeft
        if (Array.isArray(v.sessions)) {
          v.sessions = v.sessions.map(s => ({
            day: s.day,
            note: this.getSessionNote(i, s.day), // ← ensure sync
            exercises: (s.exercises || []).map(e => ({
              name: e.name || '',
              sets: (e.sets === null || e.sets === undefined || e.sets === '') ? null : Number(e.sets),
              reps: e.reps || '',
              rpe:  e.rpe  || '',
              notes: e.notes || ''
            }))
          }));
        }

        const hasRows =
          (v.focus && v.focus.trim().length) ||
          (Array.isArray(v.sessions) && v.sessions.some(s => (s.exercises||[]).length || (s.note||'').trim().length));

        if (hasRows) out[k] = v;
      }
      return JSON.stringify(out);
    },
  }
}
</script>
@endpush
