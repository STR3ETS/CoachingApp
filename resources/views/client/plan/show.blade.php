@extends('layouts.app')
@section('title','Mijn schema')

@section('content')
<h1 class="text-2xl font-bold mb-2">Mijn trainingsschema</h1>
<p class="text-sm text-black opacity-80 font-medium mb-10">Bekijk hier jouw volledige trainingsschema.</p>

@if(!$plan)
    <p class="text-sm text-gray-500">Nog geen schema beschikbaar. Je coach werkt eraan.</p>
@else
    <x-plan.viewer :plan="$plan" />
@endif
@endsection
