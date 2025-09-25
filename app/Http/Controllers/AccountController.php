<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function showSetPassword(Request $request)
    {
        // Als al gezet, gewoon naar dashboard
        if (auth()->user()->password_set_at) {
            return redirect()->route('client.dashboard');
        }
        return view('account.set-password');
    }

    public function storeSetPassword(Request $request)
    {
        $data = $request->validate([
            'password' => ['required','string','min:8','confirmed'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($data['password']);
        $user->password_set_at = now();
        $user->save();

        return redirect()->route('client.dashboard')->with('status','Wachtwoord ingesteld ✅');
    }
}
