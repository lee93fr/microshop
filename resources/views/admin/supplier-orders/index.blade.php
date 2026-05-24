@extends('layouts.admin')
@section('title', 'Bons fournisseur')
@section('header', 'Bons de commande fournisseur')

@section('header-actions')
    <a href="{{ route('admin.supplier-orders.create') }}" class="btn-primary">+ Nouveau bon</a>
@endsection

@section('content')

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

{{-- Compteur + reset --}}
<div class="flex items-center justify-between mb-3">
    <span class="text-sm text-gray-500">
        {{ $supplierOrders->total() }} bon{{ $supplierOrders->total() > 1 ? 's' : '' }}
        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.supplier-orders.index') }}" class="ml-2 text-xs text-indigo-600 hover:underline">✕ Réinitialiser les filtres</a>
        @endif
    </span>
</div>

<div class="card overflow-hidden overflow-x-auto">
    <form method="GET" id="so-filter-form">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">

        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left">
                        <a href="{{ $sortUrl('reference') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                            Référence {!! $sortIcon('reference') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Commandes</th>
                    <th class="px-6 py-3 text-left">
                        <a href="{{ $sortUrl('status') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                            Statut {!! $sortIcon('status') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <a href="{{ $sortUrl('sent_at') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                            Envoyé le {!! $sortIcon('sent_at') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Via</th>
                    <th class="px-6 py-3"></th>
                </tr>
                {{-- Ligne de filtres --}}
                <tr class="bg-white border-b border-gray-100">
                    <td class="px-4 py-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="BON-…" class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 font-mono focus:ring-indigo-500 focus:border-indigo-500"
                               oninput="debounceSOFilter()">
                    </td>
                    <td class="px-4 py-2"></td>
                    <td class="px-4 py-2">
                        <select name="status" onchange="document.getElementById('so-filter-form').submit()"
                                class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Tous</option>
                            <option value="draft"     @selected(request('status') === 'draft')>Brouillon</option>
                            <option value="sent"      @selected(request('status') === 'sent')>Envoyé</option>
                            <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmé</option>
                        </select>
                    </td>
                    <td class="px-4 py-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500"
                               onchange="document.getElementById('so-filter-form').submit()">
                    </td>
                    <td class="px-4 py-2"></td>
                    <td class="px-4 py-2 text-right">
                        <button type="submit" class="text-xs text-indigo-600 hover:underline font-medium">Filtrer</button>
                    </td>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($supplierOrders as $so)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-mono font-medium text-indigo-600">
                        <a href="{{ route('admin.supplier-orders.show', $so) }}" class="hover:underline">{{ $so->reference }}</a>
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ count($so->order_ids) }} commande(s)</td>
                    <td class="px-6 py-3">
                        <span class="{{ $so->status === 'confirmed' ? 'badge-green' : ($so->status === 'sent' ? 'badge-blue' : 'badge-gray') }}">
                            {{ $so->status_label }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-gray-400 text-xs">{{ $so->sent_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs uppercase">{{ $so->sent_via ?? '—' }}</td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.supplier-orders.show', $so) }}" class="text-indigo-600 hover:underline text-xs font-medium">Voir →</a>
                            <form method="POST" action="{{ route('admin.supplier-orders.destroy', $so) }}" class="inline"
                                  onsubmit="return confirm('Supprimer le bon {{ $so->reference }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 text-xs font-medium">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-16 text-center text-gray-400">Aucun bon fournisseur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </form>
    <div class="px-6 py-4 border-t border-gray-100">{{ $supplierOrders->links() }}</div>
</div>

<script>
let debounceSOTimer;
function debounceSOFilter() {
    clearTimeout(debounceSOTimer);
    debounceSOTimer = setTimeout(() => document.getElementById('so-filter-form').submit(), 400);
}
</script>
@endsection
