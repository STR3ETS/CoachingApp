<?php

namespace App\Http\Controllers;

use App\Models\TrainingPlan;

class ClientPlanController extends Controller
{
    public function show()
    {
        $client = auth()->user()->client;
        abort_if(!$client, 403);

        // Pak eerst laatste definitieve plan; anders laatste concept
        $plan = TrainingPlan::where('client_id', $client->id)
            ->orderByDesc('is_final')
            ->latest()
            ->first();

        // Niks te tonen? Gewoon de view renderen met lege staat
        if (!$plan) {
            return view('client.plan.show', compact('plan'));
        }

        // ✅ Policy: mag de ingelogde user dit plan zien?
        $this->authorize('view', $plan);

        // Optie: JSON downloaden via ?format=json
        if (request('format') === 'json') {
            return response()->json($plan->plan_json, 200, [
                'Content-Disposition' => 'attachment; filename="mijn_plan_'.$plan->id.'.json"'
            ]);
        }

        return view('client.plan.show', compact('plan'));
    }
}
