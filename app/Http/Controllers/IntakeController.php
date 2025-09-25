<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientProfile;
use App\Models\Intake;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class IntakeController extends Controller
{
    /**
     * Alle stappen die je in de UI toont (incl. e-mail).
     * Let op: 'email' is toegevoegd en hoort vroeg in de flow te staan.
     */
    protected array $steps = [
        'name','email','birthdate','address','gender','height_cm','weight_kg','injuries','goals',
        'period_weeks','frequency','background','facilities','materials','work_hours',
        'heartrate','test_12min','test_5k','coach_preference'
    ];

    /**
     * Minimale set die vereist is om de intake "complete" te maken.
     * (Dit voorkomt dat je ALLE velden verplicht maakt.)
     */
    protected array $requiredForComplete = [
        'name', 'email', 'goals', 'period_weeks', 'frequency'
        // 'birthdate' kun je erbij zetten als je die ook verplicht wil.
    ];

    /**
     * Start: maak of hergebruik een prospect-intake ZONDER login.
     * We onthouden de intake in de sessie zodat je geen stapel clients maakt.
     */
    public function start(Request $request)
    {
        if ($id = $request->session()->get('intake_id')) {
            if ($existing = Intake::find($id)) {
                return view('intake.conversation', ['intake' => $existing, 'steps' => $this->steps]);
            }
        }

        // Nieuwe prospect client (user_id is null)
        $client = Client::create([
            'user_id' => null,
            'status'  => 'prospect',
        ]);

        // Nieuwe intake
        $intake = Intake::create([
            'client_id'   => $client->id,
            'payload'     => [],
            'is_complete' => false,
        ]);

        // Bewaar id in de sessie voor vervolgrequests
        $request->session()->put('intake_id', $intake->id);

        return view('intake.conversation', [
            'intake' => $intake,
            'steps'  => $this->steps,
        ]);
    }

    // ↓↓↓ voeg toe binnen de IntakeController class ↓↓↓
    private function syncClientProfileFromPayload(Intake $intake): void
    {
        $payload = $intake->payload ?? [];

        $allowed = [
            'birthdate','address','gender','height_cm','weight_kg','injuries','goals',
            'period_weeks','frequency','background','facilities','materials','work_hours',
            'heartrate','test_12min','test_5k','coach_preference',
        ];

        $dataForProfile = [];
        foreach ($allowed as $k) {
            $v = $payload[$k] ?? null;

            // Leeg → null
            if ($v === '' || $v === []) { $v = null; }

            // ENUM gender: leeg naar null
            if ($k === 'gender' && ($v === '' || $v === null)) {
                $v = null;
            }

            // Datum
            if ($k === 'birthdate' && !empty($v)) {
                try { $v = \Carbon\Carbon::parse($v)->format('Y-m-d'); } catch (\Throwable $e) { $v = null; }
            }

            // Numeriek
            if (in_array($k, ['height_cm','period_weeks'], true)) {
                $v = ($v !== null && $v !== '') ? (int)$v : null;
            }
            if ($k === 'weight_kg') {
                $v = ($v !== null && $v !== '') ? (float)$v : null;
            }

            // JSON velden normaliseren
            if (in_array($k, ['injuries','goals','frequency','heartrate','test_12min','test_5k'], true)) {
                if (is_string($v)) {
                    $decoded = json_decode($v, true);
                    $v = json_last_error() === JSON_ERROR_NONE
                        ? $decoded
                        : collect(explode(',', $v))->map(fn($s)=>trim($s))->filter()->values()->all();
                }
            }

            $dataForProfile[$k] = $v;
        }

        \App\Models\ClientProfile::updateOrCreate(
            ['client_id' => $intake->client_id],
            $dataForProfile
        );
    }

    /**
     * Sla een enkele stap op (AJAX).
     * Verwacht: intake_id, step, value
     */
    public function storeStep(Request $request)
    {
        try {
            // 0) Inkomende request loggen
            \Log::info('Intake.storeStep: incoming', [
                'intake_id' => $request->input('intake_id'),
                'step'      => $request->input('step'),
                'value_type'=> gettype($request->input('value')),
            ]);

            $data = $request->validate([
                'intake_id' => ['required','exists:intakes,id'],
                'step'      => ['required','string', \Illuminate\Validation\Rule::in($this->steps)],
                'value'     => ['nullable'],
            ]);

            $intake  = \App\Models\Intake::findOrFail($data['intake_id']);
            $payload = $intake->payload ?? [];
            $step    = $data['step'];
            $value   = $data['value'];

            // === Normalisatie ===
            if (in_array($step, ['goals','injuries'], true)) {
                if (is_string($value)) {
                    $value = collect(explode(',', $value))
                        ->map(fn($s)=>trim($s))->filter()->values()->all();
                }
                $value = is_array($value) ? array_values(array_filter($value)) : [];
            }
            if ($step === 'period_weeks') {
                $iv = (int) $value;
                $value = in_array($iv, [12, 24], true) ? $iv : 12; // << DEFAULT 12
            }
            if ($step === 'height_cm')  { $value = $value !== null ? (int)$value  : null; }
            if ($step === 'weight_kg')  { $value = $value !== null ? (float)$value: null; }
            if (in_array($step, ['frequency','heartrate','test_12min','test_5k'], true)) {
                if (!is_array($value)) $value = null;
            }

            // Payload opslaan
            $payload[$step] = $value;
            $intake->payload = $payload;

            // Complete-check
            $intake->is_complete = collect($this->requiredForComplete)->every(function($k) use ($payload) {
                switch ($k) {
                    case 'email':
                        return isset($payload['email']) && filter_var($payload['email'], FILTER_VALIDATE_EMAIL);
                    case 'goals':
                        return isset($payload['goals']) && is_array($payload['goals']) && count($payload['goals']) > 0;
                    case 'period_weeks':
                        $pw = isset($payload['period_weeks']) ? (int)$payload['period_weeks'] : 12; // << default 12
                        return in_array($pw, [12, 24], true);
                    case 'frequency':
                        $f = $payload['frequency'] ?? null;
                        return is_array($f)
                            && (int)($f['sessions_per_week'] ?? 0) > 0
                            && (int)($f['minutes_per_session'] ?? 0) > 0;
                    default:
                        return isset($payload[$k]) && $payload[$k] !== null && $payload[$k] !== '';
                }
            });

            $intake->save();

            // Profiel direct updaten na elke stap (dus niet wachten tot complete)
            try {
                $this->syncClientProfileFromPayload($intake);
                \Log::info('Intake.storeStep: profile synced after step', [
                    'intake_id' => $intake->id,
                    'client_id' => $intake->client_id,
                    'step'      => $step,
                ]);
            } catch (\Throwable $e) {
                \Log::error('Intake.storeStep: profile sync failed', [
                    'intake_id' => $intake->id,
                    'client_id' => $intake->client_id,
                    'message'   => $e->getMessage(),
                ]);
            }

            \Log::info('Intake.storeStep: step stored', [
                'intake_id'   => $intake->id,
                'step'        => $step,
                'value_sample'=> is_scalar($value) ? $value : (is_array($value) ? array_slice($value,0,3) : gettype($value)),
                'is_complete' => (bool)$intake->is_complete,
                'payload_keys'=> array_keys($payload),
            ]);

            // Profiel-sync + user koppeling + login (laat dit lopen zodra complete true is)
            if ($intake->is_complete) {
                \Log::info('Intake.storeStep: intake COMPLETE → syncing profile', [
                    'intake_id' => $intake->id,
                    'client_id' => $intake->client_id,
                ]);

                // data mappen
                $allowed = [
                    'birthdate','address','gender','height_cm','weight_kg','injuries','goals',
                    'period_weeks','frequency','background','facilities','materials','work_hours',
                    'heartrate','test_12min','test_5k','coach_preference',
                ];

                $dataForProfile = [];
                foreach ($allowed as $k) {
                    $v = $payload[$k] ?? null;

                    if ($v === '' || $v === []) { $v = null; }
                    if ($k === 'gender' && ($v === '' || $v === null)) { $v = null; }

                    if ($k === 'birthdate' && !empty($v)) {
                        try { $v = \Carbon\Carbon::parse($v)->format('Y-m-d'); } catch (\Throwable $e) { $v = null; }
                    }

                    if (in_array($k, ['height_cm','period_weeks'], true)) {
                        $v = ($v !== null && $v !== '') ? (int)$v : null;
                    }
                    if ($k === 'weight_kg') {
                        $v = ($v !== null && $v !== '') ? (float)$v : null;
                    }

                    if (in_array($k, ['injuries','goals','frequency','heartrate','test_12min','test_5k'], true)) {
                        if (is_string($v)) {
                            $decoded = json_decode($v, true);
                            if (json_last_error() === JSON_ERROR_NONE) $v = $decoded;
                            else $v = collect(explode(',', $v))->map(fn($s)=>trim($s))->filter()->values()->all();
                        }
                    }

                    $dataForProfile[$k] = $v;
                }

                \Log::info('Intake.storeStep: profile upsert payload', [
                    'client_id' => $intake->client_id,
                    'keys'      => array_keys($dataForProfile),
                ]);

                \App\Models\ClientProfile::updateOrCreate(
                    ['client_id' => $intake->client_id],
                    $dataForProfile
                );

                \Log::info('Intake.storeStep: profile upserted OK', ['client_id' => $intake->client_id]);

                // User aanmaken/koppelen + inloggen
                $email = $payload['email'] ?? null;
                $name  = $payload['name']  ?? 'Client';

                if ($email) {
                    $user = \App\Models\User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name'     => $name,
                            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                            'role'     => 'client',
                        ]
                    );

                    $client = \App\Models\Client::find($intake->client_id);
                    if ($client && !$client->user_id) {
                        $client->user_id = $user->id;
                        $client->save();
                        \Log::info('Intake.storeStep: client linked to user', [
                            'client_id' => $client->id,
                            'user_id'   => $user->id,
                        ]);
                    }

                    \Illuminate\Support\Facades\Auth::login($user);
                    \Log::info('Intake.storeStep: user logged in', ['user_id' => $user->id]);
                }
            }

            return response()->json(['ok' => true, 'complete' => (bool)$intake->is_complete]);

        } catch (\Throwable $e) {
            \Log::error('Intake.storeStep: unhandled exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                // 'trace' => $e->getTraceAsString(), // optioneel
            ]);

            // Geef de fout terug aan je fetch() (die we net hebben aangepast om raw te tonen)
            return response()->json([
                'ok'      => false,
                'message' => 'Server error: '.$e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }
}
