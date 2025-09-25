@extends('layouts.app')
@section('title','Nieuwe thread')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Nieuwe thread</h1>

<form method="POST" action="{{ route('client.threads.store') }}" class="max-w-lg">
    @csrf
    <label class="block text-sm mb-1">Onderwerp (optioneel)</label>
    <input name="subject" class="w-full border rounded px-3 py-2 mb-3" placeholder="Bijv. Vraag over schema">
    <button class="px-4 py-2 bg-black text-white rounded">Aanmaken</button>
</form>
@endsection
