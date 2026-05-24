<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $allowed   = ['name', 'email', 'role', 'created_at'];
        $sort      = in_array($request->sort, $allowed) ? $request->sort : null;
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $users = User::query()
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($request->search).'%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($request->search).'%']);
            }))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', (bool) $request->is_active))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->orderByDesc('is_active')
            ->when($sort, fn ($q) => $q->orderBy($sort, $direction),
                          fn ($q) => $q->orderByRaw("CASE role WHEN 'super_admin' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END")->orderBy('name'))
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'sort', 'direction'));
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Impossible de modifier le rôle d\'un super administrateur.');
        }

        $request->validate(['role' => 'required|in:client,fournisseur,admin']);
        $user->update(['role' => $request->role]);

        return back()->with('success', "Rôle de {$user->name} mis à jour.");
    }

    public function toggleActive(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Impossible de désactiver un super administrateur.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Compte de {$user->name} {$status}.");
    }

    public function updateEmail(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Impossible de modifier l\'email d\'un super administrateur.');
        }

        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update([
            'email'         => $request->email,
            'pending_email' => null,
        ]);

        return back()->with('success', "Email de {$user->name} mis à jour.");
    }

    public function forcePasswordChange(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Impossible de forcer le changement de mot de passe d\'un super administrateur.');
        }

        $user->update(['must_change_password' => true]);

        return back()->with('success', "{$user->name} devra changer son mot de passe à la prochaine connexion.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Impossible de supprimer un super administrateur.');
        }
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Impossible de supprimer votre propre compte.');
        }
        if ($user->is_active) {
            return back()->with('error', 'Désactivez le compte avant de pouvoir le supprimer.');
        }

        $name = $user->name;
        $user->cartItems()->delete();
        $user->anonymize();
        $user->delete();

        return back()->with('success', "Compte de {$name} supprimé.");
    }
}
