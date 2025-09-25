<?php

namespace App\Services;

use App\Models\Client;

class PlanGenerator
{
    /**
     * Genereer een concept-trainingsplan op basis van intake.
     */
    public static function generate(Client $client, array $intakePayload, int $weeks = 12): array
    {
        $plan = [];
        for ($w = 1; $w <= $weeks; $w++) {
            $plan["week_$w"] = [
                'focus' => 'Engine + Hyrox skills',
                'sessions' => [
                    ['type'=>'Run Intervals','duration_min'=>40+$w,'intensity'=>'Z2→Z3'],
                    ['type'=>'Sled Push/Pull','rounds'=>6,'notes'=>'Technique & pacing'],
                    ['type'=>'Row + Burpee combo','duration_min'=>30,'notes'=>'Caps @ RPE 7'],
                ],
            ];
        }
        return $plan;
    }
}
