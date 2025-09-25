<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Http;

class PlanGeneratorAi
{
    public static function generateWeek(
        Client $client,
        array $payload,
        int $weekNumber,
        array $constraints = []
    ): array {
        $profile = $client->profile;

        $ctx = [
            'name'        => $payload['name'] ?? optional($client->user)->name,
            'gender'      => $profile->gender ?? null,
            'height_cm'   => $profile->height_cm ?? null,
            'weight_kg'   => $profile->weight_kg ?? null,
            'goals'       => self::asArray($profile->goals ?? $payload['goals'] ?? []),
            'injuries'    => self::asArray($profile->injuries ?? $payload['injuries'] ?? []),
            'frequency'   => self::asObject($profile->frequency ?? $payload['frequency'] ?? null),
            'heartrate'   => self::asObject($profile->heartrate ?? $payload['heartrate'] ?? null),
            'test_12min'  => self::asObject($profile->test_12min ?? $payload['test_12min'] ?? null),
            'test_5k'     => self::asObject($profile->test_5k ?? $payload['test_5k'] ?? null),
            'facilities'  => $profile->facilities ?? null,
            'materials'   => $profile->materials ?? null,
            'work_hours'  => $profile->work_hours ?? null,
        ];

        $daysAllowed = ['Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag','Zondag'];
        $sessionsPerWeek   = max(1, min(7, (int)($constraints['sessions_per_week']   ?? 3)));
        $minutesPerSession = max(15, min(180,(int)($constraints['minutes_per_session'] ?? 60)));
        $preferDays        = $constraints['prefer_days'] ?? null;

        $system = <<<SYS
        Je bent een professionele HYROX coach. Genereer het schema voor ÉÉN week (week {$weekNumber}).
        GEEF ALLEEN ÉÉN JSON-OBJECT terug, GEEN uitleg, GEEN markdown.

        Regels:
        - JSON schema: {"focus": string, "sessions":[ {"day":"Maandag|Dinsdag|Woensdag|Donderdag|Vrijdag|Zaterdag|Zondag", "exercises":[{"name":string,"sets":int,"reps":string,"rpe"?:string,"notes"?:string}] }, ... ]}
        - Aantal sessions: EXACT {$sessionsPerWeek}.
        - Dagen: ALLEMAAL UNIEK en ALLEEN uit ["Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag","Zaterdag","Zondag"].
        - Respecteer blessurepreventie en progressieve overload.
        - Pas de zwaarte/duur aan op ongeveer {$minutesPerSession} min per sessie.
        - Gebruik bestaande faciliteiten/materialen als relevant.
        - NEDERLANDS voor focus en oefeningnamen.
        SYS;

        $userContent = [
            'client'      => $ctx,
            'constraints' => [
                'week' => $weekNumber,
                'sessions_per_week'   => $sessionsPerWeek,
                'minutes_per_session' => $minutesPerSession,
                'days_allowed'        => $daysAllowed,
                'prefer_days'         => $preferDays,
            ],
            'output_schema' => [
                'type'       => 'object',
                'required'   => ['focus','sessions'],
                'properties' => [
                    'focus'    => ['type'=>'string'],
                    'sessions' => [
                        'type'=>'array',
                        'minItems'=>$sessionsPerWeek,
                        'maxItems'=>$sessionsPerWeek,
                        'items'=>[
                            'type'=>'object',
                            'required'=>['day','exercises'],
                            'properties'=>[
                                'day'=>['type'=>'string','enum'=>$daysAllowed],
                                'exercises'=>[
                                    'type'=>'array',
                                    'minItems'=>1,
                                    'items'=>[
                                        'type'=>'object',
                                        'required'=>['name','sets','reps'],
                                        'properties'=>[
                                            'name'=>['type'=>'string'],
                                            'sets'=>['type'=>'integer'],
                                            'reps'=>['type'=>'string'],
                                            'rpe' =>['type'=>'string'],
                                            'notes'=>['type'=>'string'],
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $resp = Http::withToken(config('ai.openai.api_key'))
            ->asJson()
            ->withOptions([
                'connect_timeout' => 10,
                'read_timeout'    => 90,
                'timeout'         => 100,
            ])
            ->retry(2, 800)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'            => config('ai.openai.model', 'gpt-4o-mini'),
                'temperature'      => 0.5,
                'response_format'  => ['type' => 'json_object'],
                'max_tokens'       => 1800,
                'messages' => [
                    ['role'=>'system','content'=>$system],
                    ['role'=>'user','content'=> json_encode($userContent, JSON_UNESCAPED_UNICODE)],
                ],
            ]);

        if (!$resp->ok()) {
            throw new \RuntimeException('OpenAI error: '.$resp->body());
        }

        $content = data_get($resp->json(), 'choices.0.message.content') ?? '{}';
        $decoded = json_decode(self::extractJson($content), true);

        if (!is_array($decoded) || !isset($decoded['sessions']) || !is_array($decoded['sessions'])) {
            throw new \RuntimeException('Kon JSON niet parsen of sessies ontbreken');
        }

        // Safeguards
        $used = [];
        $clean = [];
        foreach ($decoded['sessions'] as $s) {
            $day = $s['day'] ?? null;
            if (!in_array($day, $daysAllowed, true)) continue;
            if (isset($used[$day])) continue;
            $used[$day] = true;

            $ex = [];
            foreach (($s['exercises'] ?? []) as $e) {
                if (!isset($e['name'], $e['sets'], $e['reps'])) continue;
                $ex[] = [
                    'name'  => (string)$e['name'],
                    'sets'  => (int)$e['sets'],
                    'reps'  => (string)$e['reps'],
                    'rpe'   => isset($e['rpe'])   ? (string)$e['rpe']   : null,
                    'notes' => isset($e['notes']) ? (string)$e['notes'] : null,
                ];
            }
            if (count($ex) === 0) continue;

            $clean[] = ['day'=>$day, 'exercises'=>$ex];
            if (count($clean) >= $sessionsPerWeek) break;
        }

        if (count($clean) < $sessionsPerWeek) {
            foreach ($daysAllowed as $d) {
                if (!isset($used[$d])) {
                    $clean[] = ['day'=>$d, 'exercises'=>[['name'=>'Kracht circuit','sets'=>3,'reps'=>'10-12','rpe'=>'7','notes'=>null]]];
                    if (count($clean) >= $sessionsPerWeek) break;
                }
            }
        }

        return [
            'focus'    => (string)($decoded['focus'] ?? 'Trainingsweek'),
            'sessions' => $clean,
        ];
    }

    protected static function asArray($v): array
    {
        if (is_string($v)) {
            $j = json_decode($v, true);
            return is_array($j) ? $j : array_filter(array_map('trim', explode(',', $v)));
        }
        return is_array($v) ? $v : [];
    }

    protected static function asObject($v): ?array
    {
        if (is_string($v)) {
            $j = json_decode($v, true);
            return is_array($j) ? $j : null;
        }
        return is_array($v) ? $v : null;
    }

    protected static function extractJson(string $s): string
    {
        $t = trim($s);
        if ($t === '') return '{}';
        if ($t[0] === '{') return $t;
        $start = strpos($t, '{');
        $end   = strrpos($t, '}');
        if ($start !== false && $end !== false && $end > $start) {
            return substr($t, $start, $end - $start + 1);
        }
        return '{}';
    }
}
