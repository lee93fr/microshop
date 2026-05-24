@extends('layouts.client')
@section('title', 'Commande ' . $order->reference)

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <a href="{{ route('client.orders.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Mes commandes</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1 font-mono">{{ $order->reference }}</h1>
        <p class="text-sm text-gray-400">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="{{ $order->status === 'delivered' ? 'badge-green' : ($order->status === 'cancelled' ? 'badge-red' : 'badge-blue') }} text-sm px-3 py-1">
            {{ $order->status_label }}
        </span>
        <span class="{{ $order->payment_status === 'paid' ? 'badge-green' : ($order->payment_status === 'partial' ? 'badge-yellow' : 'badge-red') }} text-sm px-3 py-1">
            {{ $order->payment_status_label }}
        </span>
        @can('cancel', $order)
        <form method="POST" action="{{ route('client.orders.cancel', $order) }}"
              onsubmit="return confirm('Annuler la commande {{ $order->reference }} ? Cette action est irréversible.')">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors">
                ✕ Annuler la commande
            </button>
        </form>
        @endcan
    </div>
</div>

@if(request('payment') === 'success')
<div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-5 py-4 text-green-800">
    ✅ Paiement reçu avec succès ! Merci pour votre commande.
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        {{-- Produits --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Produits commandés</h2>
            </div>
            <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-50">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produit</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Qté</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Prix</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $item->product->name }}</td>
                        <td class="px-6 py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right text-gray-600">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-3 text-right font-semibold text-gray-900">{{ number_format($item->line_total, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    @if($order->discount > 0)
                    <tr>
                        <td colspan="3" class="px-6 py-2 text-right text-gray-500">
                            Remise{{ $order->promo_code_label ? ' (code '.$order->promo_code_label.')' : '' }}
                        </td>
                        <td class="px-6 py-2 text-right text-red-600">-{{ number_format($order->discount, 2, ',', ' ') }} €</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right font-bold text-gray-900">Total</td>
                        <td class="px-6 py-3 text-right font-bold text-lg text-gray-900">{{ number_format($order->total, 2, ',', ' ') }} €</td>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>

        {{-- Paiement --}}
        @if($order->payment_status !== 'paid')
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-3">💳 Paiement</h2>
            @if($order->payment_method === 'rib')
            <div class="bg-blue-50 rounded-xl p-4 text-sm">
                <p class="font-semibold text-blue-900 mb-2">Coordonnées pour le virement :</p>
                <p><span class="text-blue-700 font-medium">Titulaire :</span> {{ \App\Models\Setting::get('rib_account_owner') }}</p>
                <p><span class="text-blue-700 font-medium">Banque :</span> {{ \App\Models\Setting::get('rib_bank_name') }}</p>
                <p><span class="text-blue-700 font-medium">IBAN :</span> <span class="font-mono">{{ \App\Models\Setting::get('rib_iban') }}</span></p>
                <p><span class="text-blue-700 font-medium">BIC :</span> <span class="font-mono">{{ \App\Models\Setting::get('rib_bic') }}</span></p>
                <p class="mt-2 text-blue-600">Référence à indiquer : <strong class="font-mono">{{ $order->reference }}</strong></p>
            </div>
            @elseif($order->payment_link)
            <a href="{{ $order->payment_link }}" target="_blank"
               class="btn-primary px-6 py-3 rounded-xl inline-flex">
                💳 Payer maintenant — {{ number_format($order->total, 2, ',', ' ') }} €
            </a>
            @else
            <p class="text-gray-500 text-sm">Un lien de paiement vous sera envoyé prochainement.</p>
            @endif
        </div>
        @endif

        {{-- Suivi --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Suivi de la commande</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($order->statusHistories as $h)
                <div class="px-6 py-3 flex items-start gap-4">
                    <div class="text-xs text-gray-400 w-32 shrink-0 font-mono mt-0.5">{{ $h->changed_at->format('d/m/Y H:i') }}</div>
                    <div class="text-sm font-medium text-gray-900">{{ \App\Models\Order::STATUS_LABELS[$h->to_status] ?? $h->to_status }}</div>
                </div>
                @empty
                <div class="px-6 py-4 text-sm text-gray-400">Aucun historique.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">📍 Livraison</h2>
            <div class="text-sm text-gray-600 space-y-0.5">
                <p>{{ $order->delivery_address }}</p>
                <p>{{ $order->delivery_postal_code }} {{ $order->delivery_city }}</p>
                <p>{{ $order->delivery_country }}</p>
            </div>
        </div>

        @if($order->notes)
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-900 mb-2">📝 Notes</h2>
            <p class="text-sm text-gray-600">{{ $order->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
