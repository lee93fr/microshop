@extends('layouts.admin')
@section('title', 'Commandes')
@section('header', 'Commandes')

@section('header-actions')
    <a href="{{ route('admin.commandes.create') }}" class="btn-primary">+ Nouvelle commande</a>
    <button onclick="document.getElementById('purge-panel').classList.toggle('hidden')"
            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
        🗑️ Purger annulées
    </button>
@endsection

@section('content')

{{-- Panneau de purge --}}
<div id="purge-panel" class="hidden mb-4 card p-5 border-red-200 bg-red-50">
    <div class="flex items-start gap-4">
        <div class="text-2xl">🗑️</div>
        <div class="flex-1">
            <h3 class="font-semibold text-red-800 mb-1">Purger les commandes annulées</h3>
            <p class="text-sm text-red-700 mb-4">
                Suppression <strong>définitive</strong> de toutes les commandes <em>Annulées</em>.
                Cette action est irréversible.
            </p>
            <form method="POST" action="{{ route('admin.orders.purge') }}"
                  onsubmit="return confirmPurge(this)">
                @csrf
                <div class="flex items-end gap-3 flex-wrap">
                    <div>
                        <label class="form-label text-red-800">Supprimer les commandes plus anciennes que</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="older_than_days" min="0" max="3650"
                                   value="30" class="form-input w-24 text-center">
                            <span class="text-sm text-red-700">jours</span>
                            <span class="text-xs text-red-500">(0 = toutes)</span>
                        </div>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
                        Purger définitivement
                    </button>
                    <button type="button"
                            onclick="document.getElementById('purge-panel').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-red-600 hover:text-red-800">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Compteur + reset --}}
<div class="flex items-center justify-between mb-3">
    <span class="text-sm text-gray-500">
        {{ $orders->total() }} commande{{ $orders->total() > 1 ? 's' : '' }}
        @if(request()->hasAny(['search','status','payment_status','user_id','date_from','date_to']))
            <a href="{{ route('admin.commandes.index') }}" class="ml-2 text-xs text-indigo-600 hover:underline">✕ Réinitialiser les filtres</a>
        @endif
    </span>
</div>

@php
    $sortUrl  = fn($col) => request()->fullUrlWithQuery([
        'sort'      => $col,
        'direction' => ($sort === $col && $direction === 'desc') ? 'asc' : 'desc',
        'page'      => 1,
    ]);
    $sortIcon = fn($col) => $sort !== $col
        ? '<span class="text-gray-300 text-xs ml-1">⇅</span>'
        : ($direction === 'asc'
            ? '<span class="text-indigo-500 text-xs ml-1">↑</span>'
            : '<span class="text-indigo-500 text-xs ml-1">↓</span>');
@endphp

<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-5 py-3 text-left">
                    <a href="{{ $sortUrl('reference') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                        Référence {!! $sortIcon('reference') !!}
                    </a>
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                <th class="px-5 py-3 text-left">
                    <a href="{{ $sortUrl('status') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                        Statut {!! $sortIcon('status') !!}
                    </a>
                </th>
                <th class="px-5 py-3 text-left">
                    <a href="{{ $sortUrl('payment_status') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                        Paiement {!! $sortIcon('payment_status') !!}
                    </a>
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Méthode</th>
                <th class="px-5 py-3 text-right">
                    <a href="{{ $sortUrl('total') }}" class="inline-flex items-center justify-end text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                        Total {!! $sortIcon('total') !!}
                    </a>
                </th>
                <th class="px-5 py-3 text-left">
                    <a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                        Date {!! $sortIcon('created_at') !!}
                    </a>
                </th>
                <th class="px-5 py-3"></th>
            </tr>
            {{-- Ligne de filtres --}}
            <tr class="bg-white border-b border-gray-100" id="order-filter-row">
                <td class="px-3 py-2">
                    <input type="text" form="order-filter-form" name="search" value="{{ request('search') }}"
                           placeholder="CMD-…" class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 font-mono focus:ring-indigo-500 focus:border-indigo-500"
                           oninput="debounceOrderFilter()">
                </td>
                <td class="px-3 py-2">
                    <select form="order-filter-form" name="user_id" onchange="document.getElementById('order-filter-form').submit()"
                            class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Tous</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(request('user_id') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2">
                    <select form="order-filter-form" name="status" onchange="document.getElementById('order-filter-form').submit()"
                            class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Tous</option>
                        @foreach(\App\Models\Order::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2">
                    <select form="order-filter-form" name="payment_status" onchange="document.getElementById('order-filter-form').submit()"
                            class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Tous</option>
                        @foreach(\App\Models\Order::PAYMENT_STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" @selected(request('payment_status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2"></td>
                <td class="px-3 py-2"></td>
                <td class="px-3 py-2" colspan="2">
                    <div class="flex gap-1 items-center">
                        <input type="date" form="order-filter-form" name="date_from" value="{{ request('date_from') }}"
                               class="flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500"
                               onchange="document.getElementById('order-filter-form').submit()">
                        <span class="text-gray-300 text-xs">→</span>
                        <input type="date" form="order-filter-form" name="date_to" value="{{ request('date_to') }}"
                               class="flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500"
                               onchange="document.getElementById('order-filter-form').submit()">
                    </div>
                </td>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            @forelse($orders as $order)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <a href="{{ route('admin.commandes.show', $order) }}" class="font-mono text-indigo-600 hover:underline font-medium">
                        {{ $order->reference }}
                    </a>
                </td>
                <td class="px-5 py-3 text-gray-700">{{ $order->user->name }}</td>
                <td class="px-5 py-3">
                    <span class="{{ $order->status === 'delivered' ? 'badge-green' : ($order->status === 'cancelled' ? 'badge-red' : 'badge-blue') }}">
                        {{ $order->status_label }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <span class="{{ $order->payment_status === 'paid' ? 'badge-green' : ($order->payment_status === 'partial' ? 'badge-yellow' : 'badge-red') }}">
                        {{ $order->payment_status_label }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-500 uppercase text-xs">{{ $order->payment_method }}</td>
                <td class="px-5 py-3 font-semibold text-gray-900 text-right">{{ number_format($order->total, 2, ',', ' ') }} €</td>
                <td class="px-5 py-3 text-gray-400 text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-5 py-3 flex items-center gap-3">
                    <a href="{{ route('admin.commandes.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Voir →</a>
                    @if($order->status === 'cancelled')
                    <form method="POST" action="{{ route('admin.orders.destroy', $order) }}"
                          onsubmit="return confirm('Supprimer la commande {{ $order->reference }} ?\nCette action est irréversible.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 text-xs font-medium transition-colors">Suppr.</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-6 py-16 text-center text-gray-400">Aucune commande trouvée.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $orders->links() }}</div>
</div>

{{-- Formulaire caché pour les filtres colonnes (utilise form="order-filter-form") --}}
<form id="order-filter-form" method="GET" class="hidden">
    <input type="hidden" name="sort" value="{{ $sort }}">
    <input type="hidden" name="direction" value="{{ $direction }}">
</form>

<script>
let debounceOrderTimer;
function debounceOrderFilter() {
    clearTimeout(debounceOrderTimer);
    debounceOrderTimer = setTimeout(() => document.getElementById('order-filter-form').submit(), 400);
}

function confirmPurge(form) {
    var days  = parseInt(form.older_than_days.value) || 0;
    var scope = days > 0 ? 'plus de ' + days + ' jour(s)' : 'TOUTES DATES CONFONDUES';
    return confirm(
        'ATTENTION — Suppression définitive\n\n' +
        'Toutes les commandes NON PAYÉES (' + scope + ') seront effacées définitivement.\n\n' +
        'Confirmer ?'
    );
}
</script>
@endsection
