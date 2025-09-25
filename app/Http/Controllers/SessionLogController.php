<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TrainingPlan;
use App\Models\TrainingSessionLog;
use Illuminate\Http\Request;

class SessionLogController extends Controller
{
    public function store(Request $request)
    {
        $user   = $request->user();
        $client = optional($user)->client;
        abort_if(!$client, 403);

        $data = $request->validate([
            'plan_id'          => ['required','integer','exists:training_plans,id'],
            'week_number'      => ['required','integer','min:1'],
            'session_index'    => ['required','integer','min:0'],
            'session_day'      => ['nullable','string','max:50'],
            'went_well'        => ['nullable','string','max:2000'],
            'went_poorly'      => ['nullable','string','max:2000'],
            'rpe'              => ['nullable','integer','between:1,10'],
            'duration_minutes' => ['nullable','integer','between:1,1000'],
            'notes'            => ['nullable','string','max:2000'],
        ]);

        // Bevestig eigenaarschap plan → voorkomt loggen op andermans plan
        $plan = TrainingPlan::where('id',$data['plan_id'])
            ->where('client_id',$client->id)
            ->firstOrFail();

        TrainingSessionLog::updateOrCreate(
            [
                'client_id'     => $client->id,
                'plan_id'       => $plan->id,
                'week_number'   => $data['week_number'],
                'session_index' => $data['session_index'],
            ],
            [
                'session_day'      => $data['session_day'] ?? null,
                'completed_at'     => now(),
                'went_well'        => $data['went_well'] ?? null,
                'went_poorly'      => $data['went_poorly'] ?? null,
                'rpe'              => $data['rpe'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'notes'            => $data['notes'] ?? null,
            ]
        );

        return back()->with('status','Sessie opgeslagen!');
    }
}
