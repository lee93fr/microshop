@extends('layouts.fournisseur')
@section('title', 'Modifier — ' . $product->name)

@section('content')
<div class="max-w-xl space-y-6">

    <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('fournisseur.products.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Retour</a>
            <h1 class="text-xl font-bold text-gray-900">{{ $product->name }}</h1>
            @if($product->category)
                <span class="text-sm text-gray-400">{{ $product->category->name }}</span>
            @endif
        </div>

        {{-- Toggle stock rapide --}}
        <form method="POST" action="{{ route('fournisseur.products.toggle-stock', $product) }}">
            @csrf
            @if($product->in_stock)
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-medium hover:bg-red-50 hover:border-red-200 hover:text-red-700 transition-colors">
                    ✓ En stock — Cliquer pour rupture
                </button>
            @else
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-red-200 bg-red-50 text-red-700 text-xs font-medium hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 transition-colors">
                    ✗ Rupture de stock — Cliquer pour remettre en stock
                </button>
            @endif
        </form>
    </div>

    <form method="POST" action="{{ route('fournisseur.products.update', $product) }}" class="space-y-5">
        @csrf @method('PATCH')

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">

            {{-- Prix d'achat --}}
            <div>
                <label class="form-label">Prix d'achat (€) *</label>
                <input type="number" name="purchase_price" step="0.01" min="0" required
                       value="{{ old('purchase_price', $product->purchase_price) }}"
                       class="form-input @error('purchase_price') border-red-500 @enderror">
                @error('purchase_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Prix conseillé --}}
            <div>
                <label class="form-label">Prix conseillé (€)</label>
                <input type="number" name="sale_price" step="0.01" min="0"
                       value="{{ old('sale_price', $product->sale_price) }}"
                       class="form-input" placeholder="0.00">
                <p class="text-xs text-gray-400 mt-1">Facultatif — prix public que vous recommandez.</p>
            </div>

            {{-- SKU --}}
            <div>
                <label class="form-label">SKU / Référence</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                       class="form-input font-mono @error('sku') border-red-500 @enderror" placeholder="REF-001">
                @error('sku')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Volume + Degré --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Volume (ml)</label>
                    <input type="number" name="volume_ml" min="1"
                           value="{{ old('volume_ml', $product->volume_ml) }}"
                           class="form-input" placeholder="750">
                    @error('volume_ml')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Degré alcool (%)</label>
                    <input type="number" name="alcohol_degree" step="0.1" min="0" max="100"
                           value="{{ old('alcohol_degree', $product->alcohol_degree) }}"
                           class="form-input" placeholder="12.5">
                    @error('alcohol_degree')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3"
                          class="form-input">{{ old('description', $product->description) }}</textarea>
            </div>

            {{-- Nouveauté --}}
            <div class="flex items-center gap-3 pt-1">
                <input type="checkbox" name="is_new" id="is_new" value="1"
                       class="rounded border-gray-300 text-amber-500 focus:ring-amber-400"
                       @checked(old('is_new', $product->is_new))>
                <label for="is_new" class="text-sm font-medium text-gray-700 flex items-center gap-2">
                    Afficher le badge
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-white tracking-wide">NEW</span>
                    (Nouveauté)
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Enregistrer</button>
            <a href="{{ route('fournisseur.products.index') }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
