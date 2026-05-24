<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ForcedPasswordController extends Controller
{
    public function show()
    {
        if (! auth()->check() || ! auth()->user()->must_change_password) {
            return redirect()->intended('/');
        }

        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password'             => $request->password,
            'must_change_password' => false,
        ]);

        return redirect()->intended('/')
            ->with('success', 'Mot de passe mis à jour avec succès.');
    }
}
