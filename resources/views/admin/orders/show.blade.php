@extends('layouts.admin')
@section('title', $order->reference)
@section('header', 'Commande ' . $order->reference)

@section('header-actions')
    @if(!in_array($order->status, ['delivered', 'cancelled']))
    <a href="{{ route('admin.orders.edit-items', $order) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
        ✏️ Modifier (rupture)
    </a>
    @endif
    <a href="{{ route('admin.orders.delivery-note', $order) }}" target="_blank"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium bg-gray-800 text-white hover:bg-gray-700 transition-colors">
        🖨️ Bon de livraison
    </a>
    <a href="{{ route('admin.commandes.index') }}" class="btn-secondary text-sm">← Retour</a>
@endsection

@section('content')
<div class="grid grid-cols-3 gap-6">

    {{-- Colonne principale --}}
    <div class="col-span-2 space-y-6">

        {{-- Produits --}}
        <div class="card overflow-hidden overflow-x-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Produits commandés</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produit</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Qté</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Prix unit.</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="col-margin px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Marge</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $item->product->name }}</td>
                        <td class="px-6 py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right text-gray-600">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-3 text-right font-semibold text-gray-900">{{ number_format($item->line_total, 2, ',', ' ') }} €</td>
                        <td class="col-margin px-6 py-3 text-right text-green-600 text-xs">+{{ number_format($item->margin, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 text-sm">
                    <tr>
                        <td colspan="3" class="px-6 py-2 text-right text-gray-500">Sous-total</td>
                        <td class="px-6 py-2 text-right font-semibold">{{ number_format($order->subtotal, 2, ',', ' ') }} €</td>
                        <td></td>
                    </tr>
                    @if($order->discount > 0)
                    <tr>
                        <td colspan="3" class="px-6 py-1 text-right text-red-500">Remise</td>
                        <td class="px-6 py-1 text-right text-red-500">-{{ number_format($order->discount, 2, ',', ' ') }} €</td>
                        <td></td>
                    </tr>
                    @endif
                    <tr class="border-t-2 border-gray-200">
                        <td colspan="3" class="px-6 py-3 text-right font-bold text-gray-900">Total</td>
                        <td class="px-6 py-3 text-right font-bold text-lg text-gray-900">{{ number_format($order->total, 2, ',', ' ') }} €</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Historique --}}
        <div class="card overflow-hidden overflow-x-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Historique des statuts</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($order->statusHistories as $h)
                <div class="px-6 py-3 flex items-start gap-4">
                    <div class="text-xs text-gray-400 w-32 shrink-0 mt-0.5 font-mono">{{ $h->changed_at->format('d/m/Y H:i') }}</div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">→ {{ \App\Models\Order::STATUS_LABELS[$h->to_status] ?? $h->to_status }}</div>
                        @if($h->comment)<div class="text-xs text-gray-500 mt-0.5">{{ $h->comment }}</div>@endif
                        @if($h->changedBy)<div class="text-xs text-gray-400">par {{ $h->changedBy->name }}</div>@endif
                    </div>
                </div>
                @empty
                <div class="px-6 py-4 text-sm text-gray-400">Aucun historique.</div>
                @endforelse
            </div>
        </div>

        {{-- Avoirs liés --}}
        @if($order->credits && $order->credits->count())
        <div class="card overflow-hidden overflow-x-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Avoirs générés</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($order->credits as $credit)
                <div class="px-6 py-3 flex items-center justify-between gap-4 text-sm">
                    <div>
                        <span class="font-mono font-semibold text-gray-800">{{ $credit->reference }}</span>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $credit->reason }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-bold text-indigo-600">{{ number_format($credit->amount, 2, ',', ' ') }} €</div>
                        @if($credit->used_amount > 0)
                            <div class="text-xs text-gray-400">Utilisé : {{ number_format($credit->used_amount, 2, ',', ' ') }} €</div>
                        @endif
                        <div class="{{ $credit->remaining > 0 ? 'text-green-600' : 'text-gray-400' }} text-xs font-medium">
                            Solde : {{ number_format($credit->remaining, 2, ',', ' ') }} €
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($order->notes || $order->supplier_notes)
        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Notes</h2>
            @if($order->notes)
            <div class="text-sm text-gray-600 mb-2"><span class="font-medium">Client :</span> {{ $order->notes }}</div>
            @endif
            @if($order->supplier_notes)
            <div class="text-sm text-gray-600"><span class="font-medium">Fournisseur :</span> {{ $order->supplier_notes }}</div>
            @endif
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">

        {{-- Client --}}
        <div class="card p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Client</h2>
            <div class="text-sm space-y-1">
                <p class="font-medium text-gray-900">{{ $order->user->name }}</p>
                <p class="text-gray-500">{{ $order->user->email }}</p>
                <p class="text-gray-500">{{ $order->user->phone }}</p>
                <div class="mt-3 pt-3 border-t border-gray-100 text-gray-600 space-y-0.5">
                    <p>{{ $order->delivery_address }}</p>
                    <p>{{ $order->delivery_postal_code }} {{ $order->delivery_city }}</p>
                    <p>{{ $order->delivery_country }}</p>
                </div>
            </div>
        </div>

        {{-- Changer statut --}}
        <div class="card p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Changer le statut</h2>
            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="space-y-3">
                @csrf @method('PATCH')
                <select name="status" class="form-input">
                    @foreach(\App\Models\Order::STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" @selected($order->status === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <textarea name="comment" rows="2" placeholder="Commentaire (optionnel)" class="form-input text-sm"></textarea>
                <button type="submit" class="btn-primary w-full justify-center">Mettre à jour</button>
            </form>
        </div>

        {{-- Paiement --}}
        <div class="card p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Paiement</h2>
            <div class="text-sm mb-4 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Méthode</span>
                    <span class="font-medium uppercase text-xs">{{ $order->payment_method }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Statut</span>
                    <span class="{{ $order->payment_status === 'paid' ? 'badge-green' : ($order->payment_status === 'partial' ? 'badge-yellow' : 'badge-red') }}">
                        {{ $order->payment_status_label }}
                    </span>
                </div>
                @if($order->payment_link)
                <div>
                    <a href="{{ $order->payment_link }}" target="_blank" class="text-indigo-600 hover:underline text-xs">Voir lien de paiement →</a>
                </div>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.orders.update-payment', $order) }}" class="space-y-2">
                @csrf @method('PATCH')
                <select name="payment_status" class="form-input">
                    @foreach(\App\Models\Order::PAYMENT_STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" @selected($order->payment_status === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="payment_method" class="form-input">
                    @foreach(['stripe' => 'Stripe', 'revolut' => 'Revolut', 'rib' => 'Virement RIB', 'cash' => 'Espèces'] as $val => $label)
                        <option value="{{ $val }}" @selected($order->payment_method === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="url" name="payment_link" value="{{ $order->payment_link }}"
                       placeholder="Lien de paiement (Revolut…)" class="form-input text-sm">
                <button type="submit" class="btn-secondary w-full justify-center text-sm">Sauvegarder paiement</button>
            </form>

            @if($order->payment_method === 'stripe')
            <form method="POST" action="{{ route('admin.orders.stripe-link', $order) }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-indigo-300 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-50 transition-colors">
                    ⚡ Générer lien Stripe
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
