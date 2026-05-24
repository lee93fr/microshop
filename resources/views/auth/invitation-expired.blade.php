<x-guest-layout>
    <div class="text-center space-y-4">
        <div class="text-4xl">⏰</div>
        <h2 class="text-lg font-semibold text-gray-900">Lien expiré</h2>
        <p class="text-sm text-gray-600">
            Ce lien d'invitation n'est plus valide (durée de vie : 7 jours).<br>
            Contactez l'administrateur pour recevoir une nouvelle invitation.
        </p>
        <a href="{{ route('login') }}" class="inline-block mt-4 text-indigo-600 hover:underline text-sm">
            Retour à la connexion
        </a>
    </div>
</x-guest-layout>
