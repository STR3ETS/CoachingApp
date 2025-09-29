@extends('layouts.app')
@section('title','Home')
@section('content')
<div class="flex flex-col md:items-center">
    <img class="fade-up max-w-[7rem] md:max-w-[10rem] mb-8" src="/assets/logo-2befit-teamverhoeven.webp" alt="Logo">
    <div class="fade-up w-full aspect-[1/1.75] md:aspect-[2/1] relative rounded-3xl overflow-hidden">
        <video class="rounded-3xl w-full h-full absolute z-1 object-cover" autoplay loop muted playsinline src="/assets/2befit-promo-horizontal.mp4"></video>
        <div class="w-full h-full p-8 absolute z-2 bg-black/25 flex flex-col gap-6">
            <h1 class="text-6xl text-white font-black">GET IN<br>SHAPE!<br>WORKOUT.</h1>
            <a href="{{ route('intake.start') }}"
            class="w-fit block p-4 bg-[#c8ab7a] hover:bg-[#a89067] transition duration-300 rounded text-white">
                <span class="font-semibold block">Start mijn intake!</span>
                <span class="text-sm opacity-90">Snel en gemakkelijk jouw trainingspakket!</span>
            </a>
            <a href="{{ route('login') }}" class="w-fit px-5 py-3 bg-black text-white rounded text-sm font-semibold">Ik heb al een account</a>
            <img class="md:max-w-[50%] absolute bottom-0 right-0" src="/assets/foto-groep.png" alt="Groep">
        </div>
    </div>
    <div class="w-full flex flex-col md:flex-row items-center justify-between mt-6">
        <p class="text-[10px] text-black opacity-[35%] font-medium">Copyright © 2BeFit x TeamVerhoeven</p>
        <p class="text-[10px] text-black opacity-[35%] font-medium">Een software in samenwerking met <a class="font-bold" href="https://www.eazyonline.nl" target="_blank">EazyOnline</a></p>
    </div>
</div>
@endsection
