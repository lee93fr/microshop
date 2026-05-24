<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function show(string $token)
    {
        $user = User::where('invitation_token', $token)->firstOrFail();

        // Expiration 7 jours
        if ($user->invitation_sent_at && $user->invitation_sent_at->diffInDays(now()) > 7) {
            return view('auth.invitation-expired');
        }

        return view('auth.invitation', compact('user', 'token'));
    }

    public function accept(Request $request, string $token)
    {
        $user = User::where('invitation_token', $token)->firstOrFail();

        if ($user->invitation_sent_at && $user->invitation_sent_at->diffInDays(now()) > 7) {
            return redirect()->route('login')
                ->with('status', 'Ce lien d\'invitation a expiré. Contactez l\'administrateur.');
        }

        $request->validate([
            'name'                  => 'required|string|max:255',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'name'               => $request->name,
            'password'           => $request->password,
            'is_active'          => true,
            'invitation_token'   => null,
            'invitation_sent_at' => null,
        ]);

        Auth::login($user);

        $redirect = match (true) {
            $user->isAdmin()        => route('admin.dashboard'),
            $user->isFournisseur()  => route('fournisseur.products.index'),
            default                 => route('catalog.index'),
        };

        return redirect($redirect)->with('success', 'Bienvenue ! Votre compte est activé.');
    }
}
