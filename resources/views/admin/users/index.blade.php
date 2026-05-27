@extends('layouts.admin')
@section('title', 'Gestion des utilisateurs')
@section('header', 'Utilisateurs')

@section('header-actions')
    <button onclick="toggleInvitePanel()" class="btn-primary gap-2">+ Inviter un utilisateur</button>
@endsection

@section('content')
<div class="space-y-4">

    {{-- Bannière lien d'invitation généré --}}
    @if(session('invitation_link'))
    <div id="invitation-link-banner" class="card p-5 border-l-4 border-green-400 bg-green-50">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-green-800 mb-1">
                    ✓ {{ session('success') }}
                </p>
                <p class="text-xs text-green-700 mb-3">
                    Partagez ce lien avec <strong>{{ session('invitation_name') }}</strong> pour qu'il/elle puisse créer son compte :
                </p>
                <div class="flex items-center gap-2">
                    <input
                        id="generated-link-input"
                        type="text"
                        value="{{ session('invitation_link') }}"
                        readonly
                        class="flex-1 text-xs font-mono bg-white border border-green-300 rounded-lg px-3 py-2 text-gray-700 select-all focus:outline-none focus:ring-2 focus:ring-green-400"
                        onclick="this.select()"
                    >
                    <button
                        type="button"
                        onclick="copyInvitationLink('generated-link-input', this)"
                        class="flex-shrink-0 inline-flex items-center gap-1.5 text-xs px-4 py-2 rounded-lg font-medium bg-green-600 text-white hover:bg-green-700 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Copier le lien
                    </button>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('invitation-link-banner').remove()" class="text-green-500 hover:text-green-700 flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @elseif(session('success'))
    <div class="card p-4 border-l-4 border-green-400 bg-green-50">
        <p class="text-sm text-green-800">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="card p-4 border-l-4 border-red-400 bg-red-50">
        <p class="text-sm text-red-800">✗ {{ session('error') }}</p>
    </div>
    @endif

    {{-- Panneau d'invitation --}}
    <div id="invite-panel" class="hidden card p-6 border-l-4 border-indigo-400">
        <h3 class="font-semibold text-gray-900 mb-4">Inviter un nouvel utilisateur</h3>
        <form method="POST" action="{{ route('admin.users.invite') }}" class="space-y-4">
            @csrf
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-44">
                    <label class="form-label">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Jean Dupont">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex-1 min-w-52">
                    <label class="form-label">Adresse email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="jean@exemple.fr">
                    @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Rôle *</label>
                    <select name="role" class="form-input">
                        <option value="client"      @selected(old('role','client') === 'client')>Client</option>
                        <option value="fournisseur" @selected(old('role') === 'fournisseur')>Fournisseur</option>
                        <option value="admin"       @selected(old('role') === 'admin')>Admin</option>
                    </select>
                    @error('role')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex flex-wrap gap-2 items-center pt-1 border-t border-gray-100">
                {{-- Bouton : envoyer l'email + générer le lien --}}
                <button type="submit" name="action" value="send" class="btn-primary inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Envoyer l'invitation par email
                </button>
                {{-- Bouton : générer le lien sans envoyer d'email --}}
                <button type="submit" name="action" value="link"
                        class="inline-flex items-center gap-1.5 text-sm px-4 py-2 rounded-lg font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Générer le lien seulement
                </button>
                <button type="button" onclick="toggleInvitePanel()" class="btn-secondary">Annuler</button>
            </div>
        </form>
    </div>

    @php
        $sortUrl  = fn($col) => request()->fullUrlWithQuery([
            'sort'      => $col,
            'direction' => ($sort === $col && $direction === 'desc') ? 'asc' : 'desc',
            'page'      => 1,
        ]);
        $sortIcon = fn($col) => $sort !== $col
            ? '<span class="text-gray-300 text-xs ml-1">⇅</span>'
            : ($direction === 'asc'
                ? '<span class="text-indigo-500 text-xs ml-1">↑</span>'
                : '<span class="text-indigo-500 text-xs ml-1">↓</span>');
    @endphp

    {{-- Compteur + reset --}}
    <div class="flex items-center justify-between">
        <span class="text-sm text-gray-500">
            {{ $users->total() }} utilisateur{{ $users->total() > 1 ? 's' : '' }}
            @if(request()->hasAny(['search','role','is_active']))
                <a href="{{ route('admin.users.index') }}" class="ml-2 text-xs text-indigo-600 hover:underline">✕ Réinitialiser les filtres</a>
            @endif
        </span>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden overflow-x-auto">
        <form method="GET" id="filter-form">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left">
                            <a href="{{ $sortUrl('name') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                                Nom {!! $sortIcon('name') !!}
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <a href="{{ $sortUrl('email') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                                Email {!! $sortIcon('email') !!}
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <a href="{{ $sortUrl('role') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                                Rôle {!! $sortIcon('role') !!}
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                                Inscription {!! $sortIcon('created_at') !!}
                            </a>
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    {{-- Ligne de filtres --}}
                    <tr class="bg-white border-b border-gray-100">
                        <td class="px-3 py-2" colspan="2">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Nom ou email…"
                                   class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500"
                                   oninput="debounceFilter()">
                        </td>
                        <td class="px-3 py-2">
                            <select name="role" onchange="document.getElementById('filter-form').submit()"
                                    class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Tous</option>
                                <option value="client"      @selected(request('role') === 'client')>Client</option>
                                <option value="fournisseur" @selected(request('role') === 'fournisseur')>Fournisseur</option>
                                <option value="admin"       @selected(request('role') === 'admin')>Admin</option>
                                <option value="super_admin" @selected(request('role') === 'super_admin')>Super Admin</option>
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                   class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500"
                                   onchange="document.getElementById('filter-form').submit()">
                        </td>
                        <td class="px-3 py-2">
                            <select name="is_active" onchange="document.getElementById('filter-form').submit()"
                                    class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Tous</option>
                                <option value="1" @selected(request('is_active') === '1')>Actifs</option>
                                <option value="0" @selected(request('is_active') === '0')>En attente / désactivés</option>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <button type="submit" class="text-xs text-indigo-600 hover:underline font-medium">Filtrer</button>
                        </td>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            <div class="flex items-center gap-2">
                                <div class="h-7 w-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                {{ $user->name }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @php
                                $roleColors = [
                                    'super_admin' => 'bg-purple-100 text-purple-700',
                                    'admin'       => 'bg-indigo-100 text-indigo-700',
                                    'fournisseur' => 'bg-amber-100 text-amber-700',
                                    'client'      => 'bg-gray-100 text-gray-600',
                                ];
                                $roleLabels = [
                                    'super_admin' => 'Super Admin',
                                    'admin'       => 'Admin',
                                    'fournisseur' => 'Fournisseur',
                                    'client'      => 'Client',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $roleLabels[$user->role] ?? $user->role }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($user->invitation_token)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    ✉ Invitation envoyée
                                </span>
                            @elseif($user->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    ● Actif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                    ● En attente
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if(!$user->isSuperAdmin())
                            <div class="flex items-center justify-end gap-2">
                                @if($user->invitation_token)
                                    <button type="button"
                                            data-copy-link="{{ route('invitation.show', $user->invitation_token) }}"
                                            onclick="copyLinkFromAttr(this)"
                                            class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        Copier le lien
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.reinvite', $user) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded-lg font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                            Renvoyer
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <select name="role" onchange="this.form.submit()"
                                                class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="client"      @selected($user->role === 'client')>Client</option>
                                            <option value="fournisseur" @selected($user->role === 'fournisseur')>Fournisseur</option>
                                            <option value="admin"       @selected($user->role === 'admin')>Admin</option>
                                        </select>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded-lg font-medium transition-colors
                                                       {{ $user->is_active
                                                          ? 'bg-red-50 text-red-600 hover:bg-red-100'
                                                          : 'bg-green-50 text-green-700 hover:bg-green-100' }}"
                                                onclick="return confirm('{{ $user->is_active ? 'Désactiver' : 'Activer' }} le compte de {{ addslashes($user->name) }} ?')">
                                            {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                    <button type="button"
                                            onclick="openEmailModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')"
                                            class="text-xs px-3 py-1.5 rounded-lg font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                        Email
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.force-password', $user) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded-lg font-medium bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors"
                                                onclick="return confirm('Forcer {{ addslashes($user->name) }} à changer son mot de passe à la prochaine connexion ?')"
                                                {{ $user->must_change_password ? 'disabled title=\'Déjà en attente de changement\'' : '' }}>
                                            {{ $user->must_change_password ? 'Mdp forcé ✓' : 'Forcer mdp' }}
                                        </button>
                                    </form>
                                    @if(!$user->is_active)
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 rounded-lg font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors"
                                                onclick="return confirm('Supprimer définitivement {{ addslashes($user->name) }} ?\n\nLes données personnelles seront anonymisées. L\'historique des commandes sera conservé.')">
                                            Supprimer
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">Aucun utilisateur trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>

    {{ $users->links() }}
</div>

{{-- Modal changement email --}}
<div id="email-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
        <h3 class="font-semibold text-gray-900">Changer l'adresse email</h3>
        <p id="email-modal-name" class="text-sm text-gray-500"></p>
        <form id="email-modal-form" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="form-label">Nouvelle adresse email *</label>
                <input type="email" name="email" id="email-modal-input" required class="form-input" placeholder="nouveau@email.fr">
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeEmailModal()" class="btn-secondary">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- Toast notification copie --}}
<div id="copy-toast" class="fixed bottom-6 right-6 z-50 hidden">
    <div class="flex items-center gap-2 bg-gray-900 text-white text-sm px-4 py-3 rounded-xl shadow-lg">
        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Lien copié dans le presse-papiers !
    </div>
</div>

<script>
let debounceTimer;
function debounceFilter() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => document.getElementById('filter-form').submit(), 400);
}

function openEmailModal(userId, userName, currentEmail) {
    document.getElementById('email-modal-name').textContent = userName + ' — ' + currentEmail;
    document.getElementById('email-modal-form').action = '/admin/utilisateurs/' + userId + '/email';
    document.getElementById('email-modal-input').value = '';
    document.getElementById('email-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('email-modal-input').focus(), 50);
}
function closeEmailModal() {
    document.getElementById('email-modal').classList.add('hidden');
}
document.getElementById('email-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEmailModal();
});

function toggleInvitePanel() {
    const panel = document.getElementById('invite-panel');
    panel.classList.toggle('hidden');
    if (!panel.classList.contains('hidden')) {
        panel.querySelector('input[name="name"]').focus();
    }
}
@if($errors->any())
document.addEventListener('DOMContentLoaded', () => {
    const panel = document.getElementById('invite-panel');
    if (panel) panel.classList.remove('hidden');
});
@endif

// ──────────────────────────────────────────────
//  Copier un lien dans le presse-papiers
// ──────────────────────────────────────────────

function showCopyToast() {
    const toast = document.getElementById('copy-toast');
    toast.classList.remove('hidden');
    clearTimeout(window._copyToastTimer);
    window._copyToastTimer = setTimeout(() => toast.classList.add('hidden'), 2500);
}

/**
 * Copie la valeur d'un <input readonly> identifié par son id.
 * Met à jour le texte du bouton brièvement.
 */
function copyInvitationLink(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        showCopyToast();
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '✓ Copié !';
            btn.disabled = true;
            setTimeout(() => {
                btn.innerHTML = original;
                btn.disabled = false;
            }, 2000);
        }
    }).catch(() => {
        // Fallback pour les navigateurs plus anciens
        input.select();
        document.execCommand('copy');
        showCopyToast();
    });
}

/**
 * Copie le lien depuis l'attribut data-copy-link du bouton dans le tableau.
 */
function copyLinkFromAttr(btn) {
    const url = btn.getAttribute('data-copy-link');
    if (!url) return;
    navigator.clipboard.writeText(url).then(() => {
        showCopyToast();
        const original = btn.innerHTML;
        btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copié !';
        btn.disabled = true;
        setTimeout(() => {
            btn.innerHTML = original;
            btn.disabled = false;
        }, 2000);
    }).catch(() => {
        // Fallback
        const tmp = document.createElement('textarea');
        tmp.value = url;
        document.body.appendChild(tmp);
        tmp.select();
        document.execCommand('copy');
        document.body.removeChild(tmp);
        showCopyToast();
    });
}
</script>
@endsection
