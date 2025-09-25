@extends('layouts.app')
@section('title',$plan->title)

@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $plan->title }} <span class="text-sm text-gray-500">(#{{ $plan->id }})</span></h1>

<x-plan.viewer :plan="$plan" />

@endsection
