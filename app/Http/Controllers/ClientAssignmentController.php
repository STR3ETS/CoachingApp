<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientAssignmentController extends Controller
{
    public function index()
    {
        $coach = auth()->user()->coach;
        abort_if(!$coach, 403);

        $clients = Client::with(['user','profile'])
            ->whereNull('coach_id')
            ->latest()
            ->get();

        return view('coach.clients.unassigned', compact('clients'));
    }

    public function claim(Client $client)
    {
        $coach = auth()->user()->coach;
        abort_if(!$coach, 403);

        // claim alleen als nog niet geclaimd
        if (is_null($client->coach_id)) {
            $client->coach_id = $coach->id;
            $client->status   = $client->status === 'prospect' ? 'active' : $client->status;
            $client->save();
        }

        return redirect()->route('coach.dashboard')->with('status', 'Client geclaimd.');
    }
}
