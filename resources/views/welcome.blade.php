@extends('layouts.app')
@section('title','Home')
@section('content')
<div class="text-center py-16">
    <h1 class="text-3xl font-bold mb-4">Hyrox Coaching</h1>
    <p class="mb-6">Start nu je intake en kies je 12- of 24-weken pakket.</p>
    <a href="{{ route('intake.start') }}" class="px-5 py-3 bg-black text-white rounded">Start intake</a>
</div>
@endsection
