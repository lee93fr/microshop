{{-- admin/promo-codes/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Codes promo')
@section('header', 'Codes promo')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Liste --}}
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Codes existants</h2>
                <span class="text-xs text-gray-400">{{ $promoCodes->total() }} au total</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Remise</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Min. achat</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Util.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Période</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($promoCodes as $promo)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono font-semibold text-gray-900">{{ $promo->code }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-green-700">
                                −{{ number_format((float) $promo->discount_amount, 2, ',', ' ') }} €
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">
                                {{ number_format((float) $promo->min_purchase, 2, ',', ' ') }} €
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-600">
                                {{ $promo->used_count }}{{ $promo->max_uses ? ' / '.$promo->max_uses : '' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                @if($promo->starts_at || $promo->expires_at)
                                    {{ $promo->starts_at?->format('d/m/Y') ?? '—' }}
                                    →
                                    {{ $promo->expires_at?->format('d/m/Y') ?? '∞' }}
                                @else
                                    <span class="text-gray-300">Sans limite</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $promo->status_label }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button"
                                        onclick='openEdit(@json($promo))'
                                        class="text-indigo-600 hover:text-indigo-800 text-xs font-medium mr-2">
                                    Modifier
                                </button>
                                <form method="POST" action="{{ route('admin.promo-codes.toggle', $promo) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button class="text-xs text-gray-500 hover:text-gray-800 mr-2">
                                        {{ $promo->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.promo-codes.destroy', $promo) }}" class="inline"
                                      onsubmit="return confirm('Supprimer le code {{ $promo->code }} ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">Aucun code promo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($promoCodes->hasPages())
            <div class="px-6 py-3 border-t border-gray-100">{{ $promoCodes->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Formulaires --}}
    <div class="space-y-4">
        {{-- Édition (masqué par défaut) --}}
        <div id="edit-panel" class="card p-6 hidden border-indigo-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Modifier <span id="edit-title" class="text-indigo-600 font-mono"></span></h2>
                <button type="button" onclick="closeEdit()" class="text-sm text-gray-400 hover:text-gray-600">✕ Annuler</button>
            </div>
            <form id="edit-form" method="POST" action="" class="space-y-3">
                @csrf @method('PATCH')
                @include('admin.promo-codes._fields', ['prefix' => 'edit'])
                <button type="submit" class="btn-primary w-full">Enregistrer</button>
            </form>
        </div>

        {{-- Création --}}
        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Nouveau code promo</h2>
            <form method="POST" action="{{ route('admin.promo-codes.store') }}" class="space-y-3">
                @csrf
                @include('admin.promo-codes._fields', ['prefix' => 'create'])
                <button type="submit" class="btn-primary w-full">Créer</button>
            </form>
        </div>
    </div>
</div>

<script>
var editRoutes = @json($promoCodes->mapWithKeys(fn ($p) => [$p->id => route('admin.promo-codes.update', $p)]));

function openEdit(promo) {
    document.getElementById('edit-title').textContent = promo.code;
    document.getElementById('edit-form').action = editRoutes[promo.id];
    document.getElementById('edit-code').value = promo.code;
    document.getElementById('edit-discount_amount').value = parseFloat(promo.discount_amount);
    document.getElementById('edit-min_purchase').value = parseFloat(promo.min_purchase);
    document.getElementById('edit-max_uses').value = promo.max_uses ?? '';
    document.getElementById('edit-starts_at').value = promo.starts_at ? promo.starts_at.substring(0,16) : '';
    document.getElementById('edit-expires_at').value = promo.expires_at ? promo.expires_at.substring(0,16) : '';
    document.getElementById('edit-is_active').checked = !!promo.is_active;
    document.getElementById('edit-panel').classList.remove('hidden');
    document.getElementById('edit-panel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeEdit() {
    document.getElementById('edit-panel').classList.add('hidden');
}
</script>
@endsection
