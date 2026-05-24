{{-- admin/categories/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Catégories')
@section('header', 'Catégories')

@section('content')
<div class="grid grid-cols-2 gap-6">
    {{-- Liste --}}
    <div class="card overflow-hidden overflow-x-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-900">Catégories existantes</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nom</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Produits</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                    <td class="px-6 py-3 text-center">
                        <span class="badge-blue">{{ $category->products_count }}</span>
                    </td>
                    <td class="px-6 py-3 text-right flex items-center justify-end gap-3">
                        <button type="button"
                                onclick="openEdit({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}')"
                                class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                            Modifier
                        </button>
                        @if($category->products_count === 0)
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                              onsubmit="return confirm('Supprimer cette catégorie ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">Aucune catégorie.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="space-y-4">
        {{-- Formulaire d'édition (masqué par défaut) --}}
        <div id="edit-panel" class="card p-6 hidden border-indigo-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Modifier la catégorie <span id="edit-title" class="text-indigo-600"></span></h2>
                <button type="button" onclick="closeEdit()" class="text-sm text-gray-400 hover:text-gray-600">✕ Annuler</button>
            </div>
            <form id="edit-form" method="POST" action="" class="space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="form-label">Nom *</label>
                    <input type="text" id="edit-name" name="name" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea id="edit-description" name="description" rows="2" class="form-input"></textarea>
                </div>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </form>
        </div>

        {{-- Créer --}}
        <div id="create-panel" class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Ajouter une catégorie</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Nom *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-500 @enderror" required>
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-input">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="btn-primary">Ajouter</button>
            </form>
        </div>
    </div>
</div>
<script>
var routes = @json($categories->mapWithKeys(fn ($c) => [$c->id => route('admin.categories.update', $c)]));

function openEdit(id, name, description) {
    document.getElementById('edit-title').textContent = name;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-form').action = routes[id];
    document.getElementById('edit-panel').classList.remove('hidden');
    document.getElementById('edit-panel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeEdit() {
    document.getElementById('edit-panel').classList.add('hidden');
}
</script>
@endsection
