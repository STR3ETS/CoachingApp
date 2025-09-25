@extends('layouts.app')
@section('content')
<div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-2">Betaling afgebroken</h1>
    <p>Geen probleem. Je kunt het later opnieuw proberen.</p>
    <a href="{{ route('intake.start') }}" class="mt-4 inline-block px-4 py-2 bg-black text-white rounded">Terug naar intake</a>
</div>
@endsection
