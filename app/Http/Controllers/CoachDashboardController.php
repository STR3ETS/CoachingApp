<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use Illuminate\Support\Facades\Auth;

class CoachDashboardController extends Controller
{
    public function index()
    {
        $coach = Auth::user()->coach ?? null;
        abort_if(!$coach, 404);

        $clients = $coach->clients()->with(['user','profile'])->get();

        $threads = Thread::where('coach_id', $coach->id)
            ->latest()
            ->get();

        return view('coach.dashboard', compact('coach', 'clients', 'threads'));
    }
}
