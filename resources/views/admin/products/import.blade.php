@extends('layouts.admin')
@section('title', 'Importer des produits')
@section('header', 'Importer des produits (CSV / Excel)')

@section('content')
<div class="max-w-2xl">
    <div class="card p-6 mb-6">
        <h2 class="font-semibold text-gray-900 mb-3">Format attendu</h2>
        <p class="text-sm text-gray-600 mb-4">Votre fichier CSV ou Excel doit contenir les colonnes suivantes :</p>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach(['nom *', 'prix_achat', 'prix_vente *', 'prix_conseille', 'categorie', 'sku', 'description', 'volume_ml', 'degre', 'unite'] as $col)
                        <th class="px-3 py-2 text-left font-mono font-semibold text-gray-600 border-b border-gray-200">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white">
                        <td class="px-3 py-2 font-mono text-gray-500 border-r border-gray-100">Bordeaux Rouge</td>
                        <td class="px-3 py-2 font-mono text-gray-500 border-r border-gray-100">12.50</td>
                        <td class="px-3 py-2 font-mono text-gray-500 border-r border-gray-100">22.00</td>
                        <td class="px-3 py-2 font-mono text-gray-500 border-r border-gray-100">28.50</td>
                        <td class="px-3 py-2 font-mono text-gray-500 border-r border-gray-100">Vins rouges</td>
                        <td class="px-3 py-2 font-mono text-gray-500 border-r border-gray-100">BDX-001</td>
                        <td class="px-3 py-2 font-mono text-gray-500 border-r border-gray-100">Description...</td>
                        <td class="px-3 py-2 font-mono text-gray-500 border-r border-gray-100">750</td>
                        <td class="px-3 py-2 font-mono text-gray-500 border-r border-gray-100">13.5</td>
                        <td class="px-3 py-2 font-mono text-gray-500">bouteille</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 mt-2">* Colonnes obligatoires. Les catégories sont créées automatiquement si elles n'existent pas.<br>
        L'import <strong>met à jour</strong> les fiches existantes (par SKU puis par nom) et crée les nouveaux produits.</p>

        <div class="mt-4 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.products.export') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 text-green-700 rounded-xl text-sm font-medium hover:bg-green-100 transition-colors">
                📥 Télécharger le fichier modèle (avec produits existants)
            </a>
        </div>
    </div>

    <div class="card p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Sélectionner votre fichier</h2>
        <form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Fichier CSV ou Excel (.csv, .xls, .xlsx)</label>
                <input type="file" name="file" accept=".csv,.xls,.xlsx"
                       class="w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100" required>
                @error('file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">📥 Importer</button>
                <a href="{{ route('admin.produits.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
