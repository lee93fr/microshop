@extends('layouts.admin')
@section('title', 'Nouveau bon fournisseur')
@section('header', 'Nouveau bon de commande fournisseur')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.supplier-orders.store') }}" class="space-y-6">
        @csrf

        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100 mb-4">
                Sélectionner les commandes à regrouper
            </h2>

            @if($orders->isEmpty())
            <p class="text-gray-400 text-sm">Aucune commande éligible (statut pending ou processing, pas encore dans un bon fournisseur).</p>
            @else
            <div class="space-y-2">
                @foreach($orders as $order)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                           class="rounded border-gray-300 text-indigo-600" {{ old('order_ids') && in_array($order->id, old('order_ids')) ? 'checked' : '' }}>
                    <div class="flex-1">
                        <span class="font-mono font-medium text-gray-900">{{ $order->reference }}</span>
                        <span class="text-gray-400 mx-2">—</span>
                        <span class="text-gray-600">{{ $order->user->name }}</span>
                        <span class="badge-blue ml-2">{{ $order->status_label }}</span>
                    </div>
                    <div class="font-semibold text-gray-900">{{ number_format($order->total, 2, ',', ' ') }} €</div>
                </label>
                @endforeach
            </div>
            @error('order_ids')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
            @endif
        </div>

        <div class="card p-6">
            <label class="form-label">Notes pour le fournisseur (optionnel)</label>
            <textarea name="notes" rows="3" class="form-input">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary px-6 py-2.5" @if($orders->isEmpty()) disabled @endif>
                Créer le bon fournisseur
            </button>
            <a href="{{ route('admin.supplier-orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
        </div>
    </form>
</div>
@endsection
