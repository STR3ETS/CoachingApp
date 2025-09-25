@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto p-6">
  <h1 class="text-2xl font-semibold mb-4">Stel je wachtwoord in</h1>
  <form method="POST" action="{{ route('account.password.store') }}" class="space-y-3">
    @csrf
    <div>
      <label class="block text-sm mb-1">Nieuw wachtwoord</label>
      <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
      @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
      <label class="block text-sm mb-1">Herhaal wachtwoord</label>
      <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required>
    </div>
    <button class="px-4 py-2 bg-black text-white rounded">Opslaan</button>
  </form>
</div>
@endsection