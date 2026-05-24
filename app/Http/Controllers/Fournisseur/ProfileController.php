<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Mail\EmailChangeVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('fournisseur.profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        auth()->user()->update($data);

        return back()->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success_password', 'Mot de passe mis à jour.');
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $user = auth()->user();
        $newEmail = $request->email;

        $user->update(['pending_email' => $newEmail]);

        $url = URL::temporarySignedRoute(
            'profile.verify-email-change',
            now()->addHours(24),
            ['id' => $user->id]
        );

        Mail::to($newEmail)->send(new EmailChangeVerification($user, $url, $newEmail));

        return back()->with('success_email', 'Un lien de confirmation a été envoyé à ' . $newEmail . '.');
    }

    public function verifyEmailChange(Request $request, int $id)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Lien de vérification invalide ou expiré.');
        }

        $user = \App\Models\User::findOrFail($id);

        if (! $user->pending_email) {
            return redirect()->route('login')->with('status', 'Aucune modification d\'email en attente.');
        }

        $user->update([
            'email'         => $user->pending_email,
            'pending_email' => null,
        ]);

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Votre adresse email a été mise à jour. Connectez-vous avec votre nouvelle adresse.');
    }
}
