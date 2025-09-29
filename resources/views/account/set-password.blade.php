@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto p-6">
  <h2 class="text-2xl font-black mb-2">Bijna klaar...</h2>
  <p class="text-sm text-black opacity-80 font-medium mb-10">Je betaling is succesvol en je abbonement is geactiveerd. Stel snel jouw wachtwoord in om toegang te krijgen tot jouw persoonlijke portaal!</p>
  <h1 class="text-xl font-bold mb-2">Stel je wachtwoord in</h1>
  <form method="POST" action="{{ route('account.password.store') }}" class="p-5 bg-white rounded-3xl border flex flex-col gap-2">
    @csrf
    <div>
      <label class="block text-sm mb-1">Nieuw wachtwoord</label>
      <input type="password" name="password" class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-[16px] md:text-sm" required>
      @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
      <label class="block text-sm mb-1">Herhaal wachtwoord</label>
      <input type="password" name="password_confirmation" class="w-full rounded-xl border-[#ededed] hover:border-[#c7c7c7] transition duration-300
                        p-3
                        focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0
                        focus:border-[#c8ab7a] text-[16px] md:text-sm" required>
    </div>
    <button
    class="px-6 py-3 bg-[#c8ab7a] hover:bg-[#a38b62] transition duration-300 text-white font-medium text-sm rounded mt-4">
        <span class="font-semibold block">Opslaan</span>
    </button>
  </form>
  <h1 class="text-2xl font-bold mb-2 flex items-center mt-10">
      <div class="flex">
          <div class="w-10 h-10 border-2 border-[#f9f6f1] rounded-full bg-black bg-cover bg-top relative bg-[url(https://cdn6.site-media.eu/images/640%2C1160x772%2B130%2B112/18694492/coachNicky-MjbAPBl6Pr1a23o9d6zbqA.webp)]"></div>
          <div class="-left-3 w-10 h-10 border-2 border-[#f9f6f1] rounded-full bg-black bg-cover bg-top relative bg-[url(https://cdn6.site-media.eu/images/576%2C1160x772%2B150%2B121/18694504/coachEline-DVsTZnUZ-eQ_EWm1zNyfww.webp)]"></div>
          <div class="-left-6 w-10 h-10 border-2 border-[#f9f6f1] rounded-full bg-black bg-cover bg-top relative bg-[url(https://cdn6.site-media.eu/images/576%2C1160x772%2B134%2B41/18694509/coachRoy-LCXiB9ufGNk2uXEnykijBA.webp)]"></div>
      </div>
      <div class="bg-white h-9 px-4 rounded-xl flex items-center relative -ml-2">
          <div class="w-4 h-4 rotate-[45deg] rounded-sm absolute -left-1 bg-white"></div>
          <p class="italic text-[10px] leading-tighter font-semibold">"Let's go! 🔥"</p>
      </div>
  </h1>
</div>
@endsection