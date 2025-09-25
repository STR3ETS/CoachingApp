<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use Illuminate\Http\Request;

class ClientThreadController extends Controller
{
    public function index()
    {
        $clientId = auth()->user()->client?->id;
        abort_if(!$clientId, 404);

        $threads = Thread::where('client_id', $clientId)->latest()->get();
        return view('threads.index', compact('threads'))->with('role','client');
    }

    public function create()
    {
        // ✅ alleen clients/coaches mogen threads maken (policy)
        $this->authorize('create', Thread::class);
        return view('threads.create')->with('role','client');
    }

    public function store(Request $request)
    {
        // ✅ policy check
        $this->authorize('create', Thread::class);

        $client = auth()->user()->client;
        abort_if(!$client, 404);

        $data = $request->validate([
            'subject' => ['nullable','string','max:150'],
        ]);

        $thread = Thread::create([
            'client_id' => $client->id,
            'coach_id'  => $client->coach_id, // kan null zijn
            'subject'   => $data['subject'] ?? null,
        ]);

        return redirect()->route('client.threads.show', $thread);
    }

    public function show(Thread $thread)
    {
        // ✅ policy: mag de ingelogde gebruiker deze thread zien?
        $this->authorize('view', $thread);

        $thread->load('messages.sender');
        return view('threads.show', compact('thread'))->with('role','client');
    }

    public function storeMessage(Request $request, Thread $thread)
    {
        // ✅ policy: mag deze gebruiker in deze thread antwoorden?
        $this->authorize('reply', $thread);

        $request->validate(['body' => ['required','string']]);

        $thread->messages()->create([
            'sender_id' => auth()->id(),
            'body'      => $request->body,
        ]);

        return back();
    }
}
