<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\TrainingPlan;
use App\Services\PlanGenerator;
use Illuminate\Http\Request;

class CoachPlanController extends Controller
{
    public function index()
    {
        $coach = auth()->user()->coach;
        abort_if(!$coach, 403);

        $plans   = TrainingPlan::where('coach_id', $coach->id)->latest()->get();
        $clients = $coach->clients()->with('user','profile')->get();

        return view('coach.plans.index', compact('plans','clients'));
    }

    public function create(Client $client)
    {
        $this->authorize('create', TrainingPlan::class);

        $coach = auth()->user()->coach;
        abort_if(!$coach || $client->coach_id !== $coach->id, 403);

        return view('coach.plans.create', compact('client'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', TrainingPlan::class);

        $coach = auth()->user()->coach;
        abort_if(!$coach, 403);

        $data = $request->validate([
            'client_id' => ['required','exists:clients,id'],
            'title'     => ['required','string','max:120'],
            'weeks'     => ['required','integer','min:1','max:52'],
            'plan_json' => ['required'], // <-- string i.p.v. array
            'is_final'  => ['nullable','boolean'],
        ]);

        // JSON decoden uit hidden field
        $plan = is_string($data['plan_json']) ? json_decode($data['plan_json'], true) : $data['plan_json'];
        if (!is_array($plan)) {
            return back()->withErrors(['plan_json' => 'Plan JSON is ongeldig'])->withInput();
        }

        // (optioneel) sanity check: bevat week_1..N
        // if (empty($plan)) { ... }

        $client = Client::findOrFail($data['client_id']);
        abort_if($client->coach_id !== $coach->id, 403);

        $planModel = TrainingPlan::create([
            'client_id' => $client->id,
            'coach_id'  => $coach->id,
            'title'     => $data['title'],
            'weeks'     => $data['weeks'],
            'plan_json' => $plan, // array opslaan (cast in model)
            'is_final'  => (bool)($data['is_final'] ?? false),
        ]);

        return redirect()->route('coach.plans.show', $planModel)->with('status','Plan opgeslagen');
    }

    public function show(TrainingPlan $plan)
    {
        $this->authorize('view', $plan);

        $coach = auth()->user()->coach;
        abort_if(!$coach || $plan->coach_id !== $coach->id, 403);

        if (request('format') === 'json') {
            return response()->json($plan->plan_json, 200, [
                'Content-Disposition' => 'attachment; filename="plan_'.$plan->id.'.json"'
            ]);
        }

        return view('coach.plans.show', compact('plan'));
    }

    public function edit(TrainingPlan $plan)
    {
        $this->authorize('update', $plan);

        $coach = auth()->user()->coach;
        abort_if(!$coach || $plan->coach_id !== $coach->id, 403);

        $client = $plan->client; // voor de header "Plan aanmaken voor ..."
        return view('coach.plans.edit', [
            'plan'   => $plan,
            'client' => $client,
        ]);
    }

    public function update(Request $request, TrainingPlan $plan)
    {
        $this->authorize('update', $plan);

        $coach = auth()->user()->coach;
        abort_if(!$coach || $plan->coach_id !== $coach->id, 403);

        $data = $request->validate([
            'title'     => ['required','string','max:120'],
            'weeks'     => ['required','integer','min:1','max:52'],
            'plan_json' => ['required'], // hidden JSON string
            'is_final'  => ['nullable','boolean'],
        ]);

        $planJson = is_string($data['plan_json']) ? json_decode($data['plan_json'], true) : $data['plan_json'];
        if (!is_array($planJson)) {
            return back()->withErrors(['plan_json' => 'Plan JSON is ongeldig'])->withInput();
        }

        $plan->update([
            'title'     => $data['title'],
            'weeks'     => $data['weeks'],
            'plan_json' => $planJson,
            'is_final'  => (bool)($data['is_final'] ?? false),
        ]);

        return redirect()->route('coach.plans.show', $plan)->with('status','Plan bijgewerkt');
    }

    public function generate(Client $client)
    {
        $this->authorize('create', TrainingPlan::class);

        $coach = auth()->user()->coach;
        abort_if(!$coach || $client->coach_id !== $coach->id, 403);

        $weeks   = (int)($client->profile->period_weeks ?? 12);
        $payload = $client->intakes()->latest()->first()?->payload ?? [];

        $draft = PlanGenerator::generate($client, $payload, $weeks);

        $plan = TrainingPlan::create([
            'client_id' => $client->id,
            'coach_id'  => $coach->id,
            'title'     => 'Hyrox schema (concept)',
            'weeks'     => $weeks,
            'plan_json' => $draft,
            'is_final'  => false,
        ]);

        return redirect()->route('coach.plans.show', $plan)->with('status','Concept gegenereerd');
    }

    public function aiDraft(\App\Models\Client $client, \Illuminate\Http\Request $request)
    {
        $coach = auth()->user()->coach;
        abort_if(!$coach || $client->coach_id !== $coach->id, 403);

        $weeks = (int) ($client->profile->period_weeks ?? $request->integer('weeks', 12));

        // Laad laatste intake payload als extra context
        $payload = $client->intakes()->latest()->first()?->payload ?? [];

        try {
            $draft = \App\Services\PlanGeneratorAi::generate($client, $payload, $weeks);
            return response()->json([
                'ok' => true,
                'plan_json' => $draft,
                'weeks' => $weeks,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok' => false,
                'message' => 'Kon AI concept niet genereren: '.$e->getMessage(),
            ], 422);
        }
    }

    public function aiDraftWeek(\App\Models\Client $client, \Illuminate\Http\Request $request)
    {
        $coach = auth()->user()->coach;
        abort_if(!$coach || $client->coach_id !== $coach->id, 403);

        $validated = $request->validate([
            'week'  => ['required','integer','min:1','max:52'],
            'sessions_per_week'   => ['nullable','integer','min:1','max:7'],
            'minutes_per_session' => ['nullable','integer','min:15','max:180'],
        ]);

        $week    = (int) $validated['week'];
        $payload = $client->intakes()->latest()->first()?->payload ?? [];

        // frequentie uit profiel veilig parsen (kan array of string zijn)
        $freq = null;
        $freqRaw = $client->profile?->frequency;

        if (is_array($freqRaw)) {
            $parsed = $freqRaw;
        } elseif (is_string($freqRaw) && $freqRaw !== '') {
            $parsed = json_decode($freqRaw, true) ?: [];
        } else {
            $parsed = [];
        }

        if ($parsed) {
            $freq = [
                'sessions_per_week'   => (int)($parsed['sessions_per_week']   ?? 3),
                'minutes_per_session' => (int)($parsed['minutes_per_session'] ?? 60),
            ];
        }

        $constraints = [
            'sessions_per_week'   => (int)($validated['sessions_per_week']   ?? ($freq['sessions_per_week']   ?? 3)),
            'minutes_per_session' => (int)($validated['minutes_per_session'] ?? ($freq['minutes_per_session'] ?? 60)),
            // eventueel: vaste voorkeursdagen, bv. ["Mon","Wed","Fri"]
            'prefer_days'         => null,
        ];

        try {
            $draft = \App\Services\PlanGeneratorAi::generateWeek(
                $client,
                $payload,
                $week,
                $constraints
            );

            // sanity: garandeer minstens 1 sessie
            if (!isset($draft['sessions']) || !is_array($draft['sessions'])) {
                $draft['sessions'] = [['day'=>'Mon','exercises'=>[]]];
            }

            return response()->json(['ok'=>true,'week'=>"week_{$week}",'data'=>$draft,'source'=>'ai']);
        } catch (\Throwable $e) {
            report($e);
            $part = [
                'focus' => 'General prep',
                'sessions' => [
                    ['day'=>'Maandag',  'exercises'=>[]],
                    ['day'=>'Woensdag', 'exercises'=>[]],
                    ['day'=>'Vrijdag',  'exercises'=>[]],
                ],
            ];
            return response()->json(['ok'=>true,'week'=>"week_{$week}",'data'=>$part,'source'=>'local_fallback']);
        }
    }
}
