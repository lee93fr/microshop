@extends('layouts.admin')
@section('title', 'Paramètres')
@section('header', 'Paramètres de l\'application')

@section('content')
<div class="max-w-3xl">
<form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
    @csrf @method('PATCH')

    {{-- Boutique --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">🏪 Boutique</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Nom de la boutique</label>
                <input type="text" name="shop_name" value="{{ $settings['shop_name'] }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Email boutique</label>
                <input type="email" name="shop_email" value="{{ $settings['shop_email'] }}" class="form-input">
            </div>
        </div>
    </div>

    {{-- Fournisseur --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">🚚 Fournisseur</h2>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Nom</label>
                <input type="text" name="supplier_name" value="{{ $settings['supplier_name'] }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="supplier_email" value="{{ $settings['supplier_email'] }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Tél. SMS</label>
                <input type="text" name="supplier_phone" value="{{ $settings['supplier_phone'] }}" class="form-input" placeholder="+33600000000">
            </div>
        </div>
    </div>

    {{-- RIB --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">🏦 Coordonnées bancaires (RIB)</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Titulaire du compte</label>
                <input type="text" name="rib_account_owner" value="{{ $settings['rib_account_owner'] }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Banque</label>
                <input type="text" name="rib_bank_name" value="{{ $settings['rib_bank_name'] }}" class="form-input">
            </div>
            <div>
                <label class="form-label">IBAN</label>
                <input type="text" name="rib_iban" value="{{ $settings['rib_iban'] }}" class="form-input font-mono">
            </div>
            <div>
                <label class="form-label">BIC</label>
                <input type="text" name="rib_bic" value="{{ $settings['rib_bic'] }}" class="form-input font-mono">
            </div>
        </div>
    </div>

    {{-- Stripe --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">💳 Stripe</h2>
        <div class="space-y-3">
            <div>
                <label class="form-label">Clé publique (pk_)</label>
                <input type="text" name="stripe_key" value="{{ $settings['stripe_key'] }}" class="form-input font-mono">
            </div>
            <div>
                <label class="form-label">Clé secrète (sk_) <span class="text-gray-400 font-normal text-xs">— vide = conserver l'actuelle</span></label>
                <input type="password" name="stripe_secret" placeholder="sk_live_••••••••" class="form-input font-mono">
            </div>
            <div>
                <label class="form-label">Webhook Secret <span class="text-gray-400 font-normal text-xs">— vide = conserver l'actuel</span></label>
                <input type="password" name="stripe_webhook_secret" placeholder="whsec_••••••••" class="form-input font-mono">
            </div>
        </div>
    </div>

    {{-- SMS --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">📱 SMS (Twilio)</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Fournisseur</label>
                <select name="sms_provider" class="form-input">
                    <option value="twilio" @selected($settings['sms_provider'] === 'twilio')>Twilio</option>
                </select>
            </div>
            <div>
                <label class="form-label">Numéro expéditeur</label>
                <input type="text" name="sms_from" value="{{ $settings['sms_from'] }}" class="form-input" placeholder="+33600000000">
            </div>
            <div class="col-span-2">
                <label class="form-label">Clé API <span class="text-gray-400 font-normal text-xs">— vide = conserver l'actuelle</span></label>
                <input type="password" name="sms_api_key" placeholder="••••••••••••••••" class="form-input font-mono">
            </div>
        </div>
    </div>

    {{-- Livraison --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">🚚 Livraison</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Frais de livraison (€)</label>
                <input type="number" name="delivery_fee" step="0.01" min="0"
                       value="{{ $settings['delivery_fee'] }}" class="form-input" placeholder="5.00">
                <p class="mt-1 text-xs text-gray-400">Montant appliqué pour les commandes en livraison à domicile. Mettre 0 pour la livraison gratuite.</p>
            </div>
        </div>
    </div>

    {{-- Modes de paiement --}}
    <div class="card p-6 space-y-6">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">💳 Modes de paiement</h2>
        <p class="text-sm text-gray-500 -mt-2">Activez ou désactivez les modes de paiement proposés aux clients lors du checkout.</p>

        @php
            $paymentMethods = [
                'stripe'  => ['💳', 'Carte bancaire (Stripe)',   'payment_stripe_enabled'],
                'bank'    => ['🏦', 'Virement bancaire (RIB)',    'payment_bank_enabled'],
                'revolut' => ['🔵', 'Revolut',                   'payment_revolut_enabled'],
                'paypal'  => ['🅿️',  'PayPal',                    'payment_paypal_enabled'],
                'cash'    => ['💵', 'Espèces',                   'payment_cash_enabled'],
            ];
        @endphp

        <div class="space-y-3">
            @foreach($paymentMethods as $key => [$icon, $label, $settingKey])
            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors">
                <input type="checkbox" name="{{ $settingKey }}" value="1"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       {{ $settings[$settingKey] ? 'checked' : '' }}>
                <span class="text-lg">{{ $icon }}</span>
                <span class="text-sm font-medium text-gray-900">{{ $label }}</span>
            </label>
            @endforeach
        </div>

        {{-- Revolut --}}
        <div class="pt-4 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🔵 Configuration Revolut</h3>
            <div>
                <label class="form-label">Identifiant Revolut (@username)</label>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">revolut.me/</span>
                    <input type="text" name="revolut_id" value="{{ $settings['revolut_id'] }}"
                           class="form-input rounded-l-none font-mono" placeholder="votreusername">
                </div>
            </div>
        </div>

        {{-- PayPal --}}
        <div class="pt-4 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">🅿️ Configuration PayPal</h3>
            <div>
                <label class="form-label">Lien de paiement PayPal</label>
                <input type="url" name="paypal_link" value="{{ $settings['paypal_link'] }}"
                       class="form-input" placeholder="https://paypal.me/votrenom">
                <p class="mt-1 text-xs text-gray-400">Ex : https://paypal.me/votreusername ou un lien PayPal Business. Les clients seront redirigés vers ce lien.</p>
            </div>
        </div>
    </div>

    {{-- SMTP --}}
    <div class="card p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">📧 Email (SMTP)</h2>
            <div class="flex items-center gap-3">
                <span id="smtp-test-result" class="text-sm hidden"></span>
                <button type="button" onclick="testSmtp()"
                        class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                    <span id="smtp-test-icon">✉</span> Tester l'envoi
                </button>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Serveur SMTP</label>
                <input type="text" name="smtp_host" value="{{ $settings['smtp_host'] }}" class="form-input font-mono" placeholder="smtp.example.com">
            </div>
            <div>
                <label class="form-label">Port</label>
                <input type="number" name="smtp_port" value="{{ $settings['smtp_port'] }}" class="form-input font-mono" placeholder="587">
            </div>
            <div>
                <label class="form-label">Chiffrement</label>
                <select name="smtp_encryption" class="form-input">
                    <option value="tls"  @selected($settings['smtp_encryption'] === 'tls')>TLS (recommandé — port 587)</option>
                    <option value="ssl"  @selected($settings['smtp_encryption'] === 'ssl')>SSL (port 465)</option>
                    <option value=""     @selected(!in_array($settings['smtp_encryption'], ['tls','ssl']))>Aucun (port 25)</option>
                </select>
            </div>
            <div>
                <label class="form-label">Nom d'utilisateur SMTP</label>
                <input type="text" name="smtp_username" value="{{ $settings['smtp_username'] }}" class="form-input font-mono" placeholder="user@example.com">
            </div>
            <div>
                <label class="form-label">Mot de passe SMTP <span class="text-gray-400 font-normal text-xs">— vide = conserver l'actuel</span></label>
                <input type="password" name="smtp_password" placeholder="••••••••" class="form-input font-mono">
            </div>
            <div>
                <label class="form-label">Adresse expéditeur</label>
                <input type="email" name="smtp_from_address" value="{{ $settings['smtp_from_address'] }}" class="form-input" placeholder="no-reply@example.com">
            </div>
            <div class="col-span-2">
                <label class="form-label">Nom expéditeur</label>
                <input type="text" name="smtp_from_name" value="{{ $settings['smtp_from_name'] }}" class="form-input" placeholder="La Tournée!">
            </div>
        </div>
    </div>

    {{-- Notifications email --}}
    <div class="card p-6 space-y-5">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">🔔 Notifications email</h2>
        <p class="text-sm text-gray-500 -mt-2">Choisissez quels emails sont envoyés automatiquement.</p>

        {{-- Email admin --}}
        <div>
            <label class="form-label">Email de notification admin</label>
            <input type="email" name="admin_notification_email"
                   value="{{ $settings['admin_notification_email'] }}"
                   class="form-input" placeholder="{{ $settings['shop_email'] ?: 'admin@exemple.fr' }}">
            <p class="mt-1 text-xs text-gray-400">Laissez vide pour utiliser l'email boutique. Reçoit les alertes nouvelles commandes.</p>
        </div>

        {{-- Toggles --}}
        @php
            $notifs = [
                'notif_order_confirmation' => ['📧', 'Confirmation de commande', 'Envoyée au client dès qu\'une commande est passée.', '1'],
                'notif_admin_new_order'    => ['🛒', 'Nouvelle commande (admin)', 'Alerte email à l\'admin à chaque nouvelle commande.', '1'],
                'notif_status_update'      => ['🔄', 'Mise à jour de statut', 'Envoyée au client quand l\'admin change le statut de sa commande.', '1'],
                'notif_payment_received'   => ['✅', 'Paiement reçu', 'Envoyée au client quand son paiement est confirmé (Stripe ou manuel).', '1'],
                'notif_order_cancelled'    => ['❌', 'Annulation de commande', 'Envoyée au client quand une commande est annulée.', '1'],
            ];
        @endphp

        <div class="space-y-3">
            @foreach($notifs as $key => [$icon, $label, $desc, $default])
            <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors">
                <input type="checkbox" name="{{ $key }}" value="1"
                       class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       {{ ($settings[$key] ?? $default) ? 'checked' : '' }}>
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $icon }} {{ $label }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $desc }}</div>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    <button type="submit" class="btn-primary px-8 py-3">Enregistrer tous les paramètres</button>
</form>
</div>

<script>
function testSmtp() {
    const btn    = document.querySelector('[onclick="testSmtp()"]');
    const icon   = document.getElementById('smtp-test-icon');
    const result = document.getElementById('smtp-test-result');

    btn.disabled = true;
    icon.textContent = '⏳';
    result.className = 'text-sm text-gray-500';
    result.textContent = 'Envoi en cours…';
    result.classList.remove('hidden');

    fetch('{{ route('admin.settings.test-smtp') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            icon.textContent = '✅';
            result.className = 'text-sm text-green-700 font-medium';
            result.textContent = data.message;
        } else {
            icon.textContent = '❌';
            result.className = 'text-sm text-red-600';
            result.textContent = data.message;
        }
    })
    .catch(() => {
        icon.textContent = '❌';
        result.className = 'text-sm text-red-600';
        result.textContent = 'Erreur réseau.';
    })
    .finally(() => { btn.disabled = false; });
}
</script>
@endsection

