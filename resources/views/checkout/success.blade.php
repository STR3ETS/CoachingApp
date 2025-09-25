@extends('layouts.app')
@section('content')
<div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-2">Betaling geslaagd ✅</h1>
    <p>Je abonnement is geactiveerd. Je coach neemt snel contact met je op.</p>
    <a href="{{ route('landing') }}" class="mt-4 inline-block px-4 py-2 bg-black text-white rounded">Terug naar home</a>
</div>
@endsection
