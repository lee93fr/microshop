@extends('layouts.admin')
@section('title', 'Modifier — ' . $order->reference)
@section('header', 'Modifier la commande ' . $order->reference)

@section('header-actions')
    <a href="{{ route('admin.commandes.show', $order) }}" class="btn-secondary text-sm">← Retour</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Alerte contexte --}}
    <div class="rounded-xl bg-amber-50 border border-amber-200 px-5 py-4 flex gap-3">
        <span class="text-xl">⚠️</span>
        <div>
            <p class="font-semibold text-amber-800">Modification pour rupture fournisseur</p>
            <p class="text-sm text-amber-700 mt-0.5">
                Réduisez ou mettez à 0 les articles indisponibles. Un avoir sera automatiquement généré
                pour la différence et crédité sur le compte de <strong>{{ $order->user->name }}</strong>.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.orders.update-items', $order) }}">
        @csrf @method('PATCH')

        {{-- Tableau des articles --}}
        <div class="card overflow-hidden overflow-x-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Articles commandés</h2>
                <span class="text-xs text-gray-400">Quantité 0 = article retiré de la commande</span>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Produit</th>
                        <th class="px-6 py-3 text-center">Qté commandée</th>
                        <th class="px-6 py-3 text-center">Nouvelle qté</th>
                        <th class="px-6 py-3 text-right">P.U.</th>
                        <th class="px-6 py-3 text-right">Avoir généré</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white" id="items-body">
                    @foreach($order->items as $item)
                    <tr id="row-{{ $item->id }}">
                        <td class="px-6 py-3 font-medium text-gray-900">
                            {{ $item->product->name }}
                            @if($item->product->volume_ml)
                                <span class="text-xs text-gray-400 ml-1">{{ $item->product->volume_ml }} ml</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center text-gray-500 font-mono">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-center">
                            <input type="number"
                                   name="quantities[{{ $item->id }}]"
                                   value="{{ $item->quantity }}"
                                   min="0"
                                   max="{{ $item->quantity }}"
                                   class="w-20 text-center form-input font-mono avoir-qty"
                                   data-original="{{ $item->quantity }}"
                                   data-price="{{ $item->unit_price }}"
                                   data-row="{{ $item->id }}"
                                   oninput="updateAvoir(this)">
                        </td>
                        <td class="px-6 py-3 text-right text-gray-500">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-3 text-right font-semibold text-indigo-600" id="avoir-{{ $item->id }}">—</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right font-semibold text-gray-700">Total avoir à générer :</td>
                        <td class="px-6 py-3 text-right font-bold text-lg text-indigo-600" id="avoir-total">0,00 €</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Motif --}}
        <div class="card p-6 space-y-3">
            <h2 class="font-semibold text-gray-900">Motif de la modification *</h2>
            <textarea name="reason" rows="3" required
                      placeholder="Ex : Rupture de stock Bordeaux Rouge 75cl chez le fournisseur — réapprovisionnement dans 2 semaines."
                      class="form-input @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
            @error('reason')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
            <p class="text-xs text-gray-400">Ce motif apparaîtra sur l'avoir envoyé au client.</p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6"
                    onclick="return confirm('Confirmer la modification ?\nUn avoir sera généré pour le montant de la différence.')">
                Enregistrer et générer l'avoir
            </button>
            <a href="{{ route('admin.commandes.show', $order) }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
var items = {};
@foreach($order->items as $item)
items[{{ $item->id }}] = { original: {{ $item->quantity }}, price: {{ $item->unit_price }} };
@endforeach

function updateAvoir(input) {
    var id       = input.dataset.row;
    var original = parseFloat(input.dataset.original);
    var price    = parseFloat(input.dataset.price);
    var newQty   = Math.max(0, Math.min(parseInt(input.value) || 0, original));
    input.value  = newQty;

    var diff = original - newQty;
    var avoir = diff * price;
    var cell  = document.getElementById('avoir-' + id);

    if (avoir > 0) {
        cell.textContent = avoir.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
        cell.className = cell.className.replace('text-gray-400', 'text-indigo-600');
        document.getElementById('row-' + id).style.background = avoir === original * price ? '#fef3f2' : '#fafafa';
    } else {
        cell.textContent = '—';
    }

    recalcTotal();
}

function recalcTotal() {
    var total = 0;
    document.querySelectorAll('.avoir-qty').forEach(function(input) {
        var original = parseFloat(input.dataset.original);
        var price    = parseFloat(input.dataset.price);
        var newQty   = parseInt(input.value) || 0;
        total += (original - newQty) * price;
    });
    document.getElementById('avoir-total').textContent =
        total > 0
            ? total.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €'
            : '0,00 €';
}
</script>
@endsection
