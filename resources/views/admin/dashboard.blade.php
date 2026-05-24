@extends('layouts.admin')
@section('title', 'Dashboard')
@section('header', 'Tableau de bord')

@section('content')

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    @foreach([
        ['label' => 'Commandes aujourd\'hui', 'value' => $stats['orders_today'],  'icon' => '📋', 'color' => 'blue'],
        ['label' => 'En attente',             'value' => $stats['pending'],        'icon' => '⏳', 'color' => 'yellow'],
        ['label' => 'Non payées',             'value' => $stats['unpaid'],         'icon' => '💰', 'color' => 'red'],
        ['label' => 'CA ce mois',             'value' => number_format($stats['revenue_month'], 2, ',', ' ') . ' €', 'icon' => '📈', 'color' => 'green'],
    ] as $kpi)
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-gray-500">{{ $kpi['label'] }}</p>
            <span class="text-2xl">{{ $kpi['icon'] }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $kpi['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Dernières commandes --}}
<div class="card overflow-hidden overflow-x-auto">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900">Dernières commandes</h2>
        <a href="{{ route('admin.commandes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Voir tout →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    @foreach(['Référence', 'Client', 'Statut', 'Paiement', 'Total', 'Date'] as $h)
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 bg-white">
                @forelse($latestOrders as $order)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3">
                        <a href="{{ route('admin.commandes.show', $order) }}"
                           class="font-mono text-indigo-600 hover:text-indigo-800 font-medium">
                            {{ $order->reference }}
                        </a>
                    </td>
                    <td class="px-6 py-3 text-gray-700">{{ $order->user?->name ?? '—' }}</td>
                    <td class="px-6 py-3">
                        <span class="{{ $order->status === 'delivered' ? 'badge-green' : ($order->status === 'cancelled' ? 'badge-red' : 'badge-blue') }}">
                            {{ $order->status_label }}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        <span class="{{ $order->payment_status === 'paid' ? 'badge-green' : ($order->payment_status === 'partial' ? 'badge-yellow' : 'badge-red') }}">
                            {{ $order->payment_status_label }}
                        </span>
                    </td>
                    <td class="px-6 py-3 font-semibold text-gray-900">{{ number_format($order->total, 2, ',', ' ') }} €</td>
                    <td class="px-6 py-3 text-gray-400 text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Aucune commande.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
