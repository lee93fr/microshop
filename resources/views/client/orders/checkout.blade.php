{{-- client/orders/checkout.blade.php --}}
@extends('layouts.client')
@section('title', 'Finaliser ma commande')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Finaliser ma commande</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('client.checkout.store') }}" class="space-y-6" id="checkout-form">
            @csrf

            {{-- Mode de livraison --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">🚚 Mode de livraison</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label id="label-pickup"
                        class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors {{ old('delivery_mode') === 'pickup' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <input type="radio" name="delivery_mode" value="pickup"
                               class="mt-0.5 text-indigo-600 focus:ring-indigo-500"
                               {{ old('delivery_mode') === 'pickup' ? 'checked' : '' }}
                               onchange="onDeliveryModeChange(this)">
                        <div>
                            <div class="font-medium text-gray-900 text-sm">🏪 Retrait sur place (Sarcelles ou Bussy-Saint-Georges)</div>
                            <div class="text-xs text-gray-500 mt-0.5">Gratuit — venez récupérer votre commande</div>
                        </div>
                    </label>
                    <label id="label-home"
                        class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors {{ old('delivery_mode', 'home') !== 'pickup' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <input type="radio" name="delivery_mode" value="home"
                               class="mt-0.5 text-indigo-600 focus:ring-indigo-500"
                               {{ old('delivery_mode', 'home') !== 'pickup' ? 'checked' : '' }}
                               onchange="onDeliveryModeChange(this)">
                        <div>
                            <div class="font-medium text-gray-900 text-sm">🚚 Livraison à domicile </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                @if($deliveryFee > 0)
                                    + {{ number_format($deliveryFee, 2, ',', ' ') }} € de frais de livraison - Uniquement en Ile-de-France
                                @else
                                    Livraison offerte
                                @endif
                            </div>
                        </div>
                    </label>
                </div>
                @error('delivery_mode')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Adresse de livraison (masquée en retrait) --}}
            <div id="address-section" class="{{ old('delivery_mode') === 'pickup' ? 'hidden' : '' }} bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">📍 Adresse de livraison</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="form-label">Adresse *</label>
                        <input type="text" name="delivery_address"
                               value="{{ old('delivery_address', auth()->user()->address) }}"
                               class="form-input @error('delivery_address') border-red-500 @enderror">
                        @error('delivery_address')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Ville *</label>
                        <input type="text" name="delivery_city"
                               value="{{ old('delivery_city', auth()->user()->city) }}"
                               class="form-input @error('delivery_city') border-red-500 @enderror">
                        @error('delivery_city')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Code postal *</label>
                        <input type="text" name="delivery_postal_code"
                               value="{{ old('delivery_postal_code', auth()->user()->postal_code) }}"
                               class="form-input @error('delivery_postal_code') border-red-500 @enderror">
                        @error('delivery_postal_code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Pays</label>
                        <input type="text" name="delivery_country"
                               value="{{ old('delivery_country', auth()->user()->country ?? 'France') }}"
                               class="form-input">
                    </div>
                </div>
            </div>

            {{-- Paiement --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">💳 Mode de paiement</h2>

                @if(empty($paymentMethods))
                <p class="text-sm text-gray-400">Aucun mode de paiement disponible. Contactez le vendeur.</p>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($paymentMethods as $val => [$icon, $label, $desc])
                    <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors
                                  {{ old('payment_method') === $val ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}"
                           onclick="onPaymentMethodChange('{{ $val }}')">
                        <input type="radio" name="payment_method" value="{{ $val }}"
                               class="mt-0.5 text-indigo-600 focus:ring-indigo-500" {{ old('payment_method') === $val ? 'checked' : '' }}>
                        <div>
                            <div class="font-medium text-gray-900 text-sm">{{ $icon }} {{ $label }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $desc }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @endif

                @error('payment_method')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror

                @if($revolutId)
                <div id="revolut-block" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <p class="text-sm text-blue-800 font-medium mb-3">
                        Payez directement via Revolut en cliquant sur le bouton ci-dessous.
                        Indiquez votre nom et numéro de commande en référence.
                    </p>
                    <a href="https://revolut.me/{{ $revolutId }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0075EB] text-white rounded-xl font-semibold text-sm hover:bg-[#005fbc] transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.25 0H8.75A8.75 8.75 0 0 0 0 8.75v6.5A8.75 8.75 0 0 0 8.75 24h6.5A8.75 8.75 0 0 0 24 15.25v-6.5A8.75 8.75 0 0 0 15.25 0Zm2.27 15.33-2.9-4.08h-1.3v4.08H11V8.67h3.35c1.88 0 3.03 1.02 3.03 2.62 0 1.23-.68 2.1-1.76 2.44l3.08 4.27h-2.18v-.67Z"/>
                        </svg>
                        Payer avec Revolut
                    </a>
                    <p class="mt-2 text-xs text-blue-600">revolut.me/{{ $revolutId }}</p>
                </div>
                @endif

                @if($paypalLink)
                <div id="paypal-block" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <p class="text-sm text-blue-800 font-medium mb-3">
                        Payez via PayPal en cliquant sur le bouton ci-dessous.
                        Indiquez votre numéro de commande en référence.
                    </p>
                    <a href="{{ $paypalLink }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#003087] text-white rounded-xl font-semibold text-sm hover:bg-[#001f5c] transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7.144 19.532l1.049-6.751.066-.396h6.288c3.416 0 5.98-1.464 6.74-4.983.08-.378.13-.74.154-1.083C20.96 8.71 19.5 10.1 17.2 10.1h-3.902c-.48 0-.89.347-.966.82L11.2 18.92l-.048.3a.97.97 0 01-.958.817H7.144zm9.412-11.97c-.08.466-.193.9-.344 1.298-.8 2.056-2.68 2.767-5.152 2.767H9.3l-.864 5.474H5.362l2.183-13.836h5.04c2.13 0 3.59.838 3.97 2.297z"/>
                        </svg>
                        Payer avec PayPal
                    </a>
                </div>
                @endif
            </div>

            {{-- Avoir --}}
            @if($availableCredit > 0)
            <div class="bg-indigo-50 rounded-2xl border border-indigo-200 p-5">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="use_credit" value="1" id="use-credit"
                           class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500"
                           {{ old('use_credit') ? 'checked' : '' }}
                           onchange="onCreditChange(this)">
                    <div>
                        <div class="font-semibold text-indigo-900">
                            🎟️ Utiliser mon avoir — {{ number_format($availableCredit, 2, ',', ' ') }} € disponible
                        </div>
                        <div class="text-sm text-indigo-700 mt-0.5">
                            Le montant sera déduit de votre total. Seul le solde restant sera à régler.
                        </div>
                    </div>
                </label>
            </div>
            @endif

            {{-- Notes --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-3">📝 Notes (optionnel)</h2>
                <textarea name="notes" rows="3" placeholder="Instructions particulières…"
                    class="form-input">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="w-full btn-primary justify-center py-4 rounded-xl text-base font-bold">
                Confirmer la commande →
            </button>
        </form>
    </div>

    {{-- Récap --}}
    <div>
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
            <h2 class="font-semibold text-gray-900 mb-4">Votre commande</h2>
            <div class="space-y-2 text-sm mb-4">
                @foreach($items as $item)
                <div class="flex justify-between text-gray-600">
                    <span class="truncate mr-2">{{ $item->product->name }} × {{ $item->quantity }}</span>
                    <span class="shrink-0 font-medium">{{ number_format($item->product->sale_price * $item->quantity, 2, ',', ' ') }} €</span>
                </div>
                @endforeach
            </div>
            <div class="pt-3 border-t border-gray-100 space-y-1 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>Sous-total</span>
                    <span>{{ number_format($total, 2, ',', ' ') }} €</span>
                </div>
                <div id="recap-fee-line" class="flex justify-between {{ old('delivery_mode') === 'pickup' ? 'hidden' : '' }}">
                    <span>Livraison</span>
                    <span id="recap-fee">{{ $deliveryFee > 0 ? number_format($deliveryFee, 2, ',', ' ').' €' : 'Offerte' }}</span>
                </div>
                @if($promo)
                <div class="flex justify-between text-green-700">
                    <span>🎁 Code « {{ $promo->code }} »</span>
                    <span>−{{ number_format($promoDiscount, 2, ',', ' ') }} €</span>
                </div>
                @endif
                <div id="recap-credit-line" class="flex justify-between text-indigo-600 {{ old('use_credit') ? '' : 'hidden' }}">
                    <span>🎟️ Avoir déduit</span>
                    <span id="recap-credit">−{{ number_format(min($availableCredit, max(0, $total + $deliveryFee - $promoDiscount)), 2, ',', ' ') }} €</span>
                </div>
            </div>
            <div class="pt-3 border-t border-gray-100 flex justify-between text-lg font-bold text-gray-900 mt-2">
                <span>Total</span>
                <span id="recap-total">{{ number_format(max(0, $total + (old('delivery_mode') === 'pickup' ? 0 : $deliveryFee) - $promoDiscount), 2, ',', ' ') }} €</span>
            </div>
        </div>
    </div>
</div>

<script>
var subtotal        = {{ $total }};
var deliveryFee     = {{ $deliveryFee }};
var availableCredit = {{ $availableCredit ?? 0 }};
var promoDiscount   = {{ $promoDiscount ?? 0 }};

function onCreditChange(cb) {
    var creditLine = document.getElementById('recap-credit-line');
    creditLine.classList.toggle('hidden', !cb.checked);
    recalcTotal();
}

function recalcTotal() {
    var modeChecked = document.querySelector('input[name="delivery_mode"]:checked');
    var isHome      = modeChecked && modeChecked.value === 'home';
    var fee         = isHome ? deliveryFee : 0;
    var creditCb    = document.getElementById('use-credit');
    var afterPromo  = Math.max(0, subtotal + fee - promoDiscount);
    var credit      = (creditCb && creditCb.checked) ? Math.min(availableCredit, afterPromo) : 0;
    var total       = Math.max(0, afterPromo - credit);
    document.getElementById('recap-total').textContent =
        total.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
}

function onPaymentMethodChange(val) {
    var revolutBlock = document.getElementById('revolut-block');
    var paypalBlock  = document.getElementById('paypal-block');
    if (revolutBlock) revolutBlock.classList.toggle('hidden', val !== 'revolut');
    if (paypalBlock)  paypalBlock.classList.toggle('hidden',  val !== 'paypal');
}

document.addEventListener('DOMContentLoaded', function() {
    var checkedPayment = document.querySelector('input[name="payment_method"]:checked');
    if (checkedPayment) onPaymentMethodChange(checkedPayment.value);
});

function onDeliveryModeChange(radio) {
    var isHome = (radio.value === 'home');

    document.getElementById('address-section').classList.toggle('hidden', !isHome);

    ['pickup', 'home'].forEach(function(v) {
        var lbl    = document.getElementById('label-' + v);
        var active = (v === radio.value);
        lbl.classList.toggle('border-indigo-600', active);
        lbl.classList.toggle('bg-indigo-50', active);
        lbl.classList.toggle('border-gray-200', !active);
    });

    document.getElementById('recap-fee-line').classList.toggle('hidden', !isHome);
    recalcTotal();
}

document.addEventListener('DOMContentLoaded', function() {
    var checked = document.querySelector('input[name="delivery_mode"]:checked');
    if (checked) onDeliveryModeChange(checked);
});
</script>
@endsection
