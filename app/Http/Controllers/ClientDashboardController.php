<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Thread;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $client = Auth::user()->client ?? null;
        abort_if(!$client, 404);

        $activeSub = Subscription::where('client_id', $client->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $threads = Thread::where('client_id', $client->id)
            ->latest()
            ->get();

        return view('client.dashboard', compact('client', 'activeSub', 'threads'));
    }
}
