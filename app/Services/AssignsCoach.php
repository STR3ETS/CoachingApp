<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Coach;

class AssignsCoach
{
    /**
     * Koppel client aan coach obv voorkeur; anders simpele fallback.
     */
    public static function assign(Client $client): ?Coach
    {
        if ($client->coach_id) {
            return $client->coach; // al gekoppeld
        }

        $pref = optional($client->profile)->coach_preference; // 'Eline' | 'Nicky' | 'Roy' | null
        $coachQuery = Coach::query()->where('is_active', true)->with('user');

        if ($pref) {
            $match = (clone $coachQuery)->whereHas('user', function($q) use ($pref){
                $q->where('name', $pref)->orWhere('email','like', strtolower($pref).'@%');
            })->first();

            if ($match) {
                $client->coach_id = $match->id;
                $client->save();
                return $match;
            }
        }

        // Fallback: simpel — pak de coach met de minste clients
        $fallback = $coachQuery->get()->sortBy(function(Coach $c){
            return $c->clients()->count();
        })->first();

        if ($fallback) {
            $client->coach_id = $fallback->id;
            $client->save();
            return $fallback;
        }

        return null;
    }
}
