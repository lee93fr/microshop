<x-guest-layout>
    <div class="mb-6 text-center">
        <p class="text-sm text-gray-600">
            Bonjour <strong>{{ $user->name }}</strong>, finalisez la création de votre compte.
        </p>
    </div>

    <form method="POST" action="{{ route('invitation.accept', $token) }}">
        @csrf

        <div>
            <x-input-label for="name" value="Votre nom" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                          :value="old('name', $user->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" class="block mt-1 w-full" type="password"
                          name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                          name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>Créer mon compte</x-primary-button>
        </div>
    </form>
</x-guest-layout>
