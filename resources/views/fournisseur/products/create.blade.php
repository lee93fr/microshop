@extends('layouts.fournisseur')
@section('title', 'Nouveau produit')

@section('content')
<div class="max-w-xl space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('fournisseur.products.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Retour</a>
        <h1 class="text-xl font-bold text-gray-900">Nouveau produit</h1>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-800">
        ℹ️ Le produit sera soumis à validation avant d'apparaître dans le catalogue.
    </div>

    <form method="POST" action="{{ route('fournisseur.products.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">

            {{-- Nom --}}
            <div>
                <label class="form-label">Nom du produit *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="form-input @error('name') border-red-500 @enderror" placeholder="Ex. Bordeaux Rouge 2020">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Catégorie --}}
            <div>
                <label class="form-label">Catégorie *</label>
                <select name="category_id" required class="form-input @error('category_id') border-red-500 @enderror">
                    <option value="">— Choisir —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Prix d'achat --}}
            <div>
                <label class="form-label">Prix d'achat (€) *</label>
                <input type="number" name="purchase_price" step="0.01" min="0" required
                       value="{{ old('purchase_price') }}"
                       class="form-input @error('purchase_price') border-red-500 @enderror" placeholder="0.00">
                @error('purchase_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Prix conseillé --}}
            <div>
                <label class="form-label">Prix conseillé (€)</label>
                <input type="number" name="sale_price" step="0.01" min="0"
                       value="{{ old('sale_price') }}"
                       class="form-input" placeholder="0.00">
                <p class="text-xs text-gray-400 mt-1">Facultatif — prix public que vous recommandez.</p>
            </div>

            {{-- SKU --}}
            <div>
                <label class="form-label">SKU / Référence</label>
                <input type="text" name="sku" value="{{ old('sku') }}"
                       class="form-input font-mono @error('sku') border-red-500 @enderror" placeholder="REF-001">
                @error('sku')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Volume + Degré --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Volume (ml)</label>
                    <input type="number" name="volume_ml" min="1"
                           value="{{ old('volume_ml') }}"
                           class="form-input" placeholder="750">
                    @error('volume_ml')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Degré alcool (%)</label>
                    <input type="number" name="alcohol_degree" step="0.1" min="0" max="100"
                           value="{{ old('alcohol_degree') }}"
                           class="form-input" placeholder="12.5">
                    @error('alcohol_degree')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Unité --}}
            <div>
                <label class="form-label">Unité</label>
                <select name="unit" class="form-input">
                    @foreach(['bouteille', 'canette', 'pack', 'fût', 'litre'] as $u)
                        <option value="{{ $u }}" @selected(old('unit', 'bouteille') === $u)>{{ ucfirst($u) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Description --}}
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3"
                          class="form-input" placeholder="Notes sur le produit…">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Soumettre le produit</button>
            <a href="{{ route('fournisseur.products.index') }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
