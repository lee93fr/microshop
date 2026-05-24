@extends('layouts.fournisseur')
@section('title', 'Mon profil')

@section('content')
<div class="max-w-xl space-y-6">

    <h1 class="text-xl font-bold text-gray-900">Mon profil</h1>

    {{-- Informations personnelles --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h2 class="font-semibold text-gray-900">Informations personnelles</h2>

        @if(session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
            ✅ {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('fournisseur.profile.update') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="form-label">Nom complet *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="form-input @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Adresse email actuelle</label>
                <input type="email" value="{{ $user->email }}" class="form-input bg-gray-50 text-gray-400" disabled>
                @if($user->pending_email)
                <p class="text-xs text-amber-600 mt-1">
                    En attente de confirmation : <strong>{{ $user->pending_email }}</strong>. Vérifiez votre nouvelle boîte mail.
                </p>
                @endif
            </div>

            <div>
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="form-input" placeholder="+33600000000">
                @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>

    {{-- Changement d'email --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h2 class="font-semibold text-gray-900">Changer l'adresse email</h2>

        @if(session('success_email'))
        <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
            ✅ {{ session('success_email') }}
        </div>
        @endif

        @if($errors->has('email'))
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
            ❌ {{ $errors->first('email') }}
        </div>
        @endif

        <p class="text-sm text-gray-500">Un lien de confirmation sera envoyé à la nouvelle adresse. Votre email actuel restera actif jusqu'à confirmation.</p>

        <form method="POST" action="{{ route('fournisseur.profile.email') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="form-label">Nouvelle adresse email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="form-input @error('email') border-red-500 @enderror"
                       placeholder="nouvelle@adresse.fr">
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Envoyer le lien de confirmation</button>
            </div>
        </form>
    </div>

    {{-- Changement de mot de passe --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h2 class="font-semibold text-gray-900">Changer le mot de passe</h2>

        @if(session('success_password'))
        <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
            ✅ {{ session('success_password') }}
        </div>
        @endif

        @if($errors->has('current_password') || $errors->has('password'))
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
            ❌ Veuillez corriger les erreurs ci-dessous.
        </div>
        @endif

        <form method="POST" action="{{ route('fournisseur.profile.password') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="form-label">Mot de passe actuel *</label>
                <input type="password" name="current_password" required
                       class="form-input @error('current_password') border-red-500 @enderror"
                       autocomplete="current-password">
                @error('current_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Nouveau mot de passe *</label>
                <input type="password" name="password" required
                       class="form-input @error('password') border-red-500 @enderror"
                       autocomplete="new-password">
                @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Confirmer le nouveau mot de passe *</label>
                <input type="password" name="password_confirmation" required
                       class="form-input" autocomplete="new-password">
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Mettre à jour le mot de passe</button>
            </div>
        </form>
    </div>
</div>
@endsection
