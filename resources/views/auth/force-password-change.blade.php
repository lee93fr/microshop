<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-gray-900">La Tournée!</h1>
        <p class="text-sm text-gray-500 mt-1">Vous devez choisir un nouveau mot de passe pour continuer.</p>
    </div>

    @if(session('warning'))
    <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-amber-800 text-sm">
        {{ session('warning') }}
    </div>
    @endif

    <form method="POST" action="{{ route('password.forced.update') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="password" value="Nouveau mot de passe *" />
            <x-text-input id="password" class="block mt-1 w-full" type="password"
                          name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe *" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                          name="password_confirmation" required autocomplete="new-password" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">
                    Se déconnecter
                </button>
            </form>
            <x-primary-button>Enregistrer le mot de passe</x-primary-button>
        </div>
    </form>
</x-guest-layout>
