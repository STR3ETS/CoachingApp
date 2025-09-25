<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeighIn;

class ClientWeighInController extends Controller
{
    public function store(Request $request)
    {
        $client = optional(auth()->user())->client;
        abort_unless($client, 403);

        $data = $request->validate([
            'date'      => ['required','date'],
            'weight_kg' => ['required','numeric','min:20','max:400'],
            'notes'     => ['nullable','string','max:255'],
        ]);

        // unieke weging per dag per client (voorkom dubbele)
        WeighIn::updateOrCreate(
            ['client_id' => $client->id, 'date' => $data['date']],
            ['weight_kg' => $data['weight_kg'], 'notes' => $data['notes'] ?? null]
        );

        return back()->with('status', 'Weging opgeslagen');
    }
}
