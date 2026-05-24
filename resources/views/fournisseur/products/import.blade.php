@extends('layouts.fournisseur')
@section('title', 'Importer des produits')

@section('content')
<div class="max-w-xl space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('fournisseur.products.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Retour</a>
        <h1 class="text-xl font-bold text-gray-900">Importer des produits</h1>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-800 space-y-1">
        <p class="font-medium">Format attendu (CSV ou Excel) :</p>
        <p>Le fichier doit avoir une ligne d'en-tête avec les colonnes :</p>
        <code class="block bg-blue-100 rounded px-2 py-1 text-xs mt-1">nom ; prix_achat ; prix_vente ; categorie ; sku ; description ; volume_ml ; degre ; unite</code>
        <p class="text-xs text-blue-600 mt-2">Les produits importés seront soumis à validation avant d'apparaître dans le catalogue.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <a href="{{ route('fournisseur.products.export') }}"
           class="inline-flex items-center gap-2 text-sm text-green-700 hover:text-green-900 font-medium">
            📥 Télécharger le catalogue actuel (CSV) pour s'en servir de modèle
        </a>
    </div>

    <form method="POST" action="{{ route('fournisseur.products.import') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div>
                <label class="form-label">Fichier CSV ou Excel *</label>
                <input type="file" name="file" accept=".csv,.xlsx,.xls" required
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-gray-200 rounded-xl p-1">
                @error('file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Importer</button>
            <a href="{{ route('fournisseur.products.index') }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
