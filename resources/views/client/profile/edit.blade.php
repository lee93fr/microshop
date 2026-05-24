{{-- client/profile/edit.blade.php --}}
@extends('layouts.client')
@section('title', 'Mon profil')

@section('content')
<div class="max-w-xl space-y-8">
    <h1 class="text-2xl font-bold text-gray-900">Mon profil</h1>

    {{-- Informations personnelles --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Informations personnelles</h2>

        @if(session('success'))
            <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                ✅ {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.profile.update') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2">
                    <label class="form-label">Adresse email</label>
                    <input type="email" value="{{ $user->email }}" class="form-input bg-gray-50 text-gray-400" disabled>
                    <p class="text-xs text-gray-400 mt-1">L'email ne peut pas être modifié ici.</p>
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="+33600000000">
                    <p class="text-xs text-gray-400 mt-1">Utilisé pour les SMS de notification.</p>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="form-input" placeholder="12 rue de la Paix">
                </div>
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Code postal</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Pays</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}" class="form-input">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl">Enregistrer</button>
            </div>
        </form>
    </div>

    {{-- Changement de mot de passe --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Changer le mot de passe</h2>

        @if(session('success_password'))
            <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                ✅ {{ session('success_password') }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.profile.password') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="form-label">Mot de passe actuel</label>
                <input type="password" name="current_password" class="form-input" autocomplete="current-password" required>
                @error('current_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-input" autocomplete="new-password" required>
                @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" name="password_confirmation" class="form-input" autocomplete="new-password" required>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl">Mettre à jour le mot de passe</button>
            </div>
        </form>
    </div>
</div>
@endsection
