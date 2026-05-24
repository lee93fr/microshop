<x-guest-layout>

    {{-- Message si redirigé depuis le catalogue --}}
    @if(request()->is('login') && url()->previous() && str_contains(url()->previous(), 'catalogue'))
    <div class="mb-4 rounded-xl bg-indigo-50 border border-indigo-200 px-4 py-3 text-indigo-800 text-sm">
        Connectez-vous pour accéder au catalogue.
    </div>
    @endif

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-gray-900">La Tournée!</h1>
        <p class="text-sm text-gray-500 mt-1">Connectez-vous pour accéder à votre espace.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Adresse email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                          :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" class="block mt-1 w-full" type="password"
                          name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                       name="remember">
                <span class="ms-2 text-sm text-gray-600">Se souvenir de moi</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                   href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @endif
            <x-primary-button class="ms-3">Se connecter</x-primary-button>
        </div>
    </form>
</x-guest-layout>
