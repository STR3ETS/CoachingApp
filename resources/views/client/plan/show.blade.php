@extends('layouts.app')
@section('title','Mijn schema')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Mijn trainingsschema</h1>

@if(!$plan)
    <p class="text-sm text-gray-500">Nog geen schema beschikbaar. Je coach werkt eraan.</p>
@else
    <x-plan.viewer :plan="$plan" />
@endif
@endsection
