@extends('layouts.admin')
@section('title', $supplierOrder->reference)
@section('header', 'Bon fournisseur ' . $supplierOrder->reference)

@section('header-actions')
    <a href="{{ route('admin.supplier-orders.pdf', $supplierOrder) }}" class="btn-secondary" target="_blank">📄 Télécharger PDF</a>
    @if($supplierOrder->status !== 'confirmed')
    <form method="POST" action="{{ route('admin.supplier-orders.confirm', $supplierOrder) }}" class="inline">
        @csrf
        <button type="submit" class="btn-primary">✅ Marquer confirmé</button>
    </form>
    @endif
    <form method="POST" action="{{ route('admin.supplier-orders.destroy', $supplierOrder) }}" class="inline"
          onsubmit="return confirm('Supprimer définitivement le bon {{ $supplierOrder->reference }} ?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors">
            🗑 Supprimer
        </button>
    </form>
@endsection

@section('content')
<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2 space-y-6">

        {{-- Produits consolidés --}}
        <div class="card overflow-hidden overflow-x-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Produits consolidés</h2>
                <p class="text-xs text-gray-400 mt-0.5">Somme de toutes les commandes incluses</p>
            </div>
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produit</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Qté totale</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Prix achat</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total achat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @php $grandTotal = 0; @endphp
                    @foreach($consolidated as $line)
                    @php $grandTotal += $line['total_cost']; @endphp
                    <tr>
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $line['product']->name }}</td>
                        <td class="px-6 py-3 text-gray-400 font-mono text-xs">{{ $line['product']->sku ?? '—' }}</td>
                        <td class="px-6 py-3 text-center font-bold text-gray-900">{{ $line['quantity'] }}</td>
                        <td class="px-6 py-3 text-right text-gray-600">{{ number_format($line['product']->purchase_price, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-3 text-right font-semibold text-gray-900">{{ number_format($line['total_cost'], 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right font-bold text-gray-900">Total fournisseur</td>
                        <td class="px-6 py-3 text-right font-bold text-lg text-gray-900">{{ number_format($grandTotal, 2, ',', ' ') }} €</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Commandes incluses --}}
        <div class="card overflow-hidden overflow-x-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Commandes incluses ({{ count($supplierOrder->order_ids) }})</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <tbody class="divide-y divide-gray-50 bg-white">
                    @foreach($orders as $order)
                    <tr>
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.commandes.show', $order) }}" class="font-mono text-indigo-600 hover:underline">{{ $order->reference }}</a>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $order->user->name }}</td>
                        <td class="px-6 py-3">
                            <span class="badge-blue">{{ $order->status_label }}</span>
                        </td>
                        <td class="px-6 py-3 text-right font-semibold">{{ number_format($order->total, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">
        <div class="card p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Informations</h2>
            <div class="text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Statut</span>
                    <span class="{{ $supplierOrder->status === 'confirmed' ? 'badge-green' : ($supplierOrder->status === 'sent' ? 'badge-blue' : 'badge-gray') }}">
                        {{ $supplierOrder->status_label }}
                    </span>
                </div>
                @if($supplierOrder->sent_at)
                <div class="flex justify-between">
                    <span class="text-gray-500">Envoyé le</span>
                    <span class="text-gray-700">{{ $supplierOrder->sent_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Via</span>
                    <span class="text-gray-700 uppercase text-xs">{{ $supplierOrder->sent_via }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Envoyer --}}
        @if($supplierOrder->status === 'draft')
        <div class="card p-5">
            <h2 class="font-semibold text-gray-900 mb-4">Envoyer au fournisseur</h2>
            <form method="POST" action="{{ route('admin.supplier-orders.send', $supplierOrder) }}" class="space-y-3">
                @csrf
                <select name="via" class="form-input">
                    <option value="both">Email + SMS</option>
                    <option value="email">Email seulement</option>
                    <option value="sms">SMS seulement</option>
                </select>
                <button type="submit" class="btn-primary w-full justify-center">📨 Envoyer</button>
            </form>
        </div>
        @endif

        @if($supplierOrder->notes)
        <div class="card p-5">
            <h2 class="font-semibold text-gray-900 mb-2">Notes</h2>
            <p class="text-sm text-gray-600">{{ $supplierOrder->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
