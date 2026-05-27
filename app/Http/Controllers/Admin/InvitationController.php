<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'name'  => 'required|string|max:255',
            'role'  => 'required|in:client,fournisseur,admin',
        ]);

        $token = Str::random(64);

        $user = User::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'role'               => $data['role'],
            'password'           => bcrypt(Str::random(32)),
            'is_active'          => false,
            'invitation_token'   => $token,
            'invitation_sent_at' => now(),
        ]);

        $url = route('invitation.show', $user->invitation_token);

        // Action : 'send' envoie l'email + affiche le lien, 'link' affiche le lien uniquement
        $action = $request->input('action', 'send');

        if ($action === 'send') {
            $this->sendInvitationEmail($user);
            return back()
                ->with('success', "Invitation envoyée à {$user->email}.")
                ->with('invitation_link', $url)
                ->with('invitation_name', $user->name);
        }

        // Génération du lien sans envoi d'email
        return back()
            ->with('success', "Lien d'invitation généré pour {$user->email}.")
            ->with('invitation_link', $url)
            ->with('invitation_name', $user->name);
    }

    public function resend(User $user)
    {
        if (!$user->invitation_token) {
            return back()->with('error', 'Cet utilisateur a déjà activé son compte.');
        }

        $user->update(['invitation_sent_at' => now()]);

        $this->sendInvitationEmail($user);

        $url = route('invitation.show', $user->invitation_token);

        return back()
            ->with('success', "Invitation renvoyée à {$user->email}.")
            ->with('invitation_link', $url)
            ->with('invitation_name', $user->name);
    }

    private function sendInvitationEmail(User $user): void
    {
        $url = route('invitation.show', $user->invitation_token);

        try {
            Mail::to($user->email)->send(new UserInvitation($user, $url));
        } catch (\Throwable $e) {
            Log::warning("Invitation email non envoyé à {$user->email} : {$e->getMessage()}");
        }
    }
}
