@extends('layouts.admin')
@section('title', 'Nouvelle commande')
@section('header', 'Nouvelle commande manuelle')

@section('content')
<div class="max-w-4xl">
<form method="POST" action="{{ route('admin.commandes.store') }}" class="space-y-6">
    @csrf

    {{-- Client + adresse --}}
    <div class="card p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Client & Livraison</h2>
            <button type="button" id="btn-for-me"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                👤 Commander pour moi-même
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="form-label">Client *</label>
                <select id="user-select" name="user_id" class="form-input" required>
                    <option value="">Sélectionner un utilisateur…</option>
                    <optgroup label="— Admins">
                        @foreach($clients->where('role', '!=', 'client') as $user)
                            <option value="{{ $user->id }}"
                                data-address="{{ $user->address }}"
                                data-city="{{ $user->city }}"
                                data-postal="{{ $user->postal_code }}"
                                data-country="{{ $user->country ?? 'France' }}"
                                @selected(old('user_id') == $user->id)>
                                {{ $user->name }} — {{ $user->email }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="— Clients">
                        @foreach($clients->where('role', 'client') as $client)
                            <option value="{{ $client->id }}"
                                data-address="{{ $client->address }}"
                                data-city="{{ $client->city }}"
                                data-postal="{{ $client->postal_code }}"
                                data-country="{{ $client->country ?? 'France' }}"
                                @selected(old('user_id') == $client->id)>
                                {{ $client->name }} — {{ $client->email }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
                @error('user_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Adresse de livraison *</label>
                <input id="field-address" type="text" name="delivery_address" value="{{ old('delivery_address') }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Ville *</label>
                <input id="field-city" type="text" name="delivery_city" value="{{ old('delivery_city') }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Code postal *</label>
                <input id="field-postal" type="text" name="delivery_postal_code" value="{{ old('delivery_postal_code') }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Pays</label>
                <input id="field-country" type="text" name="delivery_country" value="{{ old('delivery_country', 'France') }}" class="form-input">
            </div>
        </div>
    </div>

    {{-- Produits --}}
    <div class="card p-6">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100 mb-4">Produits</h2>
        <table class="min-w-full text-sm mb-4">
            <thead>
                <tr class="text-xs font-semibold text-gray-500 uppercase">
                    <th class="text-left py-2 pr-4">Produit</th>
                    <th class="text-center py-2 px-2 w-24">Qté</th>
                    <th class="text-right py-2 px-2 w-32">Prix vente (€)</th>
                    <th class="text-right py-2 px-2 w-32">Prix achat (€)</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody id="items-tbody">
                <tr>
                    <td class="pr-4 py-1">
                        <select name="items[0][product_id]" class="form-input product-select">
                            <option value="">Sélectionner…</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-sale="{{ $p->sale_price }}" data-purchase="{{ $p->purchase_price }}">
                                    {{ $p->name }} ({{ $p->category->name }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-2 py-1"><input type="number" name="items[0][quantity]" value="1" min="1" class="form-input text-center"></td>
                    <td class="px-2 py-1"><input type="number" name="items[0][unit_price]" step="0.01" class="form-input text-right" placeholder="0.00"></td>
                    <td class="px-2 py-1"><input type="number" name="items[0][purchase_price]" step="0.01" class="form-input text-right" placeholder="0.00"></td>
                    <td class="pl-2 py-1"></td>
                </tr>
            </tbody>
        </table>

        <template id="item-row-template">
            <tr>
                <td class="pr-4 py-1">
                    <select name="items[__IDX__][product_id]" class="form-input product-select">
                        <option value="">Sélectionner…</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-sale="{{ $p->sale_price }}" data-purchase="{{ $p->purchase_price }}">
                                {{ $p->name }} ({{ $p->category->name }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-2 py-1"><input type="number" name="items[__IDX__][quantity]" value="1" min="1" class="form-input text-center"></td>
                <td class="px-2 py-1"><input type="number" name="items[__IDX__][unit_price]" step="0.01" class="form-input text-right" placeholder="0.00"></td>
                <td class="px-2 py-1"><input type="number" name="items[__IDX__][purchase_price]" step="0.01" class="form-input text-right" placeholder="0.00"></td>
                <td class="pl-2 py-1"><button type="button" data-remove-row class="text-red-400 hover:text-red-600 font-bold text-lg leading-none">×</button></td>
            </tr>
        </template>

        <div class="flex items-center justify-between">
            <button type="button" id="add-item-row" class="btn-secondary text-sm">+ Ajouter un produit</button>
            <div class="text-base font-bold text-gray-900">Total : <span id="order-total-display">0.00 €</span></div>
        </div>
    </div>

    {{-- Paiement + statut --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">Paiement & Statut</h2>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="form-label">Méthode de paiement *</label>
                <select name="payment_method" class="form-input" required>
                    @foreach(['stripe' => 'Stripe', 'revolut' => 'Revolut', 'rib' => 'Virement RIB', 'cash' => 'Espèces'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('payment_method') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Statut paiement *</label>
                <select name="payment_status" class="form-input" required>
                    @foreach(\App\Models\Order::PAYMENT_STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" @selected(old('payment_status', 'unpaid') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Statut commande *</label>
                <select name="status" class="form-input" required>
                    @foreach(\App\Models\Order::STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', 'pending') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Remise (€)</label>
                <input type="number" name="discount" step="0.01" min="0" value="{{ old('discount', 0) }}" class="form-input">
            </div>
            <div class="col-span-2">
                <label class="form-label">Lien de paiement (Revolut…)</label>
                <input type="url" name="payment_link" value="{{ old('payment_link') }}" class="form-input" placeholder="https://...">
            </div>
            <div>
                <label class="form-label">Notes client</label>
                <textarea name="notes" rows="2" class="form-input">{{ old('notes') }}</textarea>
            </div>
            <div class="col-span-2">
                <label class="form-label">Notes fournisseur</label>
                <textarea name="supplier_notes" rows="2" class="form-input">{{ old('supplier_notes') }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="btn-primary px-6 py-2.5">Créer la commande</button>
        <a href="{{ route('admin.commandes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
    </div>
</form>
</div>

<script>
(function () {
    var meId      = {{ $me->id }};
    var meAddress = @json($me->address ?? '');
    var meCity    = @json($me->city ?? '');
    var mePostal  = @json($me->postal_code ?? '');
    var meCountry = @json($me->country ?? 'France');

    function fillAddress(address, city, postal, country) {
        document.getElementById('field-address').value = address || '';
        document.getElementById('field-city').value    = city    || '';
        document.getElementById('field-postal').value  = postal  || '';
        document.getElementById('field-country').value = country || 'France';
    }

    // Bouton "Pour moi-même"
    document.getElementById('btn-for-me').addEventListener('click', function () {
        document.getElementById('user-select').value = meId;
        fillAddress(meAddress, meCity, mePostal, meCountry);
    });

    // Auto-remplissage adresse au changement de sélection utilisateur
    document.getElementById('user-select').addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        if (opt && opt.dataset.address !== undefined) {
            fillAddress(opt.dataset.address, opt.dataset.city, opt.dataset.postal, opt.dataset.country);
        }
    });

    // Auto-remplissage prix à la sélection d'un produit
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('product-select')) return;
        var opt = e.target.options[e.target.selectedIndex];
        if (!opt.value) return;
        var row      = e.target.closest('tr');
        var saleIn   = row.querySelector('[name*="unit_price"]');
        var purIn    = row.querySelector('[name*="purchase_price"]');
        if (saleIn)  saleIn.value  = parseFloat(opt.dataset.sale     || 0).toFixed(2);
        if (purIn)   purIn.value   = parseFloat(opt.dataset.purchase || 0).toFixed(2);
        updateOrderTotal();
    });
})();
</script>
@endsection
