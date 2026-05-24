{{-- client/orders/index.blade.php --}}
@extends('layouts.client')
@section('title', 'Mes commandes')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Mes commandes</h1>

@if($orders->isEmpty())
<div class="text-center py-20 text-gray-400">
    <div class="text-6xl mb-4">📋</div>
    <p class="text-lg mb-6">Vous n'avez pas encore passé de commande.</p>
    <a href="{{ route('catalog.index') }}" class="btn-primary px-8 py-3 rounded-xl">Commander maintenant</a>
</div>
@else
<div class="space-y-4">
    @foreach($orders as $order)
    <div class="bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-shadow">
        <a href="{{ route('client.orders.show', $order) }}" class="block p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <div class="font-semibold text-gray-900 font-mono">{{ $order->reference }}</div>
                    <div class="text-sm text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                </div>
                <div class="flex sm:flex-col sm:text-right items-center sm:items-end gap-3 sm:gap-1">
                    <div class="font-bold text-gray-900 text-lg">{{ number_format($order->total, 2, ',', ' ') }} €</div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="{{ $order->status === 'delivered' ? 'badge-green' : ($order->status === 'cancelled' ? 'badge-red' : 'badge-blue') }}">
                            {{ $order->status_label }}
                        </span>
                        <span class="{{ $order->payment_status === 'paid' ? 'badge-green' : ($order->payment_status === 'partial' ? 'badge-yellow' : 'badge-red') }}">
                            {{ $order->payment_status_label }}
                        </span>
                    </div>
                </div>
            </div>
        </a>
        @can('cancel', $order)
        <div class="px-5 pb-4 -mt-1">
            <form method="POST" action="{{ route('client.orders.cancel', $order) }}"
                  onsubmit="return confirm('Annuler la commande {{ $order->reference }} ?')">
                @csrf
                <button type="submit"
                    class="text-xs font-medium text-red-500 hover:text-red-700 hover:underline transition-colors">
                    ✕ Annuler cette commande
                </button>
            </form>
        </div>
        @endcan
    </div>
    @endforeach
</div>
<div class="mt-6">{{ $orders->links() }}</div>
@endif
@endsection
