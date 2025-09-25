<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class CoachClientController extends Controller
{
    public function show(Client $client)
    {
        // Alleen eigen clients
        $coach = auth()->user()->coach;
        abort_if(!$coach || $client->coach_id !== $coach->id, 403);

        // Eager loads (voorkom N+1)
        $client->load([
            'user:id,name,email',
            'profile',                         // intake/profiel
            'subscriptions' => function ($q) { // alle subs, sorteer
                $q->orderByDesc('created_at');
            },
            'trainingPlans' => function ($q) {
                $q->orderByDesc('created_at');
            },
            'threads' => function ($q) {
                $q->latest();
            },
            'weighIns' => function ($q) {
                $q->orderByDesc('date')->limit(12);
            },
            'payments' => function ($q) {
                $q->orderByDesc('created_at')->limit(20);
            },
        ]);

        // KPI’s / samenvattingen
        $activeSub    = $client->subscriptions->firstWhere('status', 'active');
        $latestWeigh  = $client->weighIns->first();
        $prevWeigh    = $client->weighIns->skip(1)->first();
        $totalRevenue = $client->payments->sum('amount'); // pas aan als je in centen werkt

        // BMI berekenen (indien data aanwezig)
        $weight = $latestWeigh->weight_kg ?? $client->profile?->weight_kg;
        $height = $client->profile?->height_cm;
        $bmi    = ($weight && $height) ? round($weight / pow($height/100, 2), 1) : null;

        // Delta gewicht (laatste vs vorige)
        $deltaKg = null;
        if ($latestWeigh && $prevWeigh) {
            $deltaKg = round($latestWeigh->weight_kg - $prevWeigh->weight_kg, 1);
        }

        return view('coach.clients.show', [
            'client'       => $client,
            'activeSub'    => $activeSub,
            'totalRevenue' => $totalRevenue,
            'latestWeigh'  => $latestWeigh,
            'bmi'          => $bmi,
            'deltaKg'      => $deltaKg,
        ]);
    }
}
