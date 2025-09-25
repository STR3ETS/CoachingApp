<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use Illuminate\Http\Request;

class CoachThreadController extends Controller
{
    public function index()
    {
        $coachId = auth()->user()->coach?->id;
        abort_if(!$coachId, 404);

        $threads = Thread::where('coach_id', $coachId)->latest()->get();
        return view('threads.index', compact('threads'))->with('role','coach');
    }

    public function show(Thread $thread)
    {
        // ✅ policy: mag de ingelogde gebruiker deze thread zien?
        $this->authorize('view', $thread);

        $thread->load('messages.sender');
        return view('threads.show', compact('thread'))->with('role','coach');
    }

    public function storeMessage(Request $request, Thread $thread)
    {
        // ✅ policy: mag de gebruiker antwoorden in deze thread?
        $this->authorize('reply', $thread);

        $request->validate(['body' => ['required','string']]);

        $thread->messages()->create([
            'sender_id' => auth()->id(),
            'body'      => $request->body,
        ]);

        return back();
    }
}
