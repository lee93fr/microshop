@extends('layouts.fournisseur')
@section('title', 'Catalogue produits')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Catalogue produits</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('fournisseur.products.import.form') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                📥 Importer Excel
            </a>
            <a href="{{ route('fournisseur.products.export') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm font-medium text-green-700 hover:bg-green-50 transition-colors">
                📤 Exporter CSV
            </a>
            <button onclick="document.getElementById('share-pdf-modal').classList.remove('hidden')"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                📄 PDF catalogue
            </button>
            <a href="{{ route('fournisseur.products.create') }}"
               class="btn-primary px-4 py-2 rounded-xl text-sm">
                + Nouveau produit
            </a>
        </div>
    </div>

    <p class="text-xs text-gray-400">
        💡 Cliquez sur un prix, SKU ou volume pour l'éditer directement dans le tableau.
    </p>

    {{-- Table avec filtres intégrés --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">

        {{-- Barre de filtres dans le tableau --}}
        <form method="GET" id="filter-form" class="flex flex-wrap gap-2 items-center px-4 py-3 border-b border-gray-100 bg-gray-50/60">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="🔍 Rechercher un produit…"
                   class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-indigo-500 focus:border-indigo-500 outline-none w-52">

            <select name="category_id" onchange="this.form.submit()"
                    class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="in_stock" onchange="this.form.submit()"
                    class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                <option value="">Tout le stock</option>
                <option value="1" @selected(request('in_stock') === '1')>En stock</option>
                <option value="0" @selected(request('in_stock') === '0')>Rupture</option>
            </select>

            <button type="submit" class="text-sm px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition-colors">
                Filtrer
            </button>

            @if(request()->hasAny(['search', 'category_id', 'in_stock']))
            <a href="{{ route('fournisseur.products.index', array_filter(['sort' => $sort, 'direction' => $direction])) }}"
               class="text-sm px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition-colors">
                ✕ Effacer
            </a>
            @endif
        </form>

        <table class="min-w-full divide-y divide-gray-200 text-sm">
            @php
                $sortUrl = fn($col) => request()->fullUrlWithQuery([
                    'sort'      => $col,
                    'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc',
                    'page'      => 1,
                ]);
                $sortIcon = fn($col) => $sort !== $col
                    ? '<span class="text-gray-300 ml-1">⇅</span>'
                    : ($direction === 'asc'
                        ? '<span class="text-indigo-500 ml-1">↑</span>'
                        : '<span class="text-indigo-500 ml-1">↓</span>');
            @endphp
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">
                        <a href="{{ $sortUrl('name') }}" class="flex items-center gap-1 hover:text-gray-700">
                            Produit {!! $sortIcon('name') !!}
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left">
                        <a href="{{ $sortUrl('category_id') }}" class="flex items-center gap-1 hover:text-gray-700">
                            Catégorie {!! $sortIcon('category_id') !!}
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left">SKU</th>
                    <th class="px-5 py-3 text-left">Volume</th>
                    <th class="px-5 py-3 text-right">
                        <a href="{{ $sortUrl('purchase_price') }}" class="flex items-center justify-end gap-1 hover:text-gray-700">
                            Prix d'achat {!! $sortIcon('purchase_price') !!}
                        </a>
                    </th>
                    <th class="px-5 py-3 text-center">Nouveauté</th>
                    <th class="px-5 py-3 text-center">Visibilité</th>
                    <th class="px-5 py-3 text-center">Stock</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($products as $product)
                <tr id="row-{{ $product->id }}" class="hover:bg-gray-50 transition-colors {{ !$product->in_stock ? 'bg-red-50/30' : '' }}">
                    <td class="px-5 py-3 font-medium text-gray-900">
                        {{ $product->name }}
                        @if($product->is_new)
                            <span class="ml-1 inline-flex px-1.5 py-0 rounded-full text-xs font-bold bg-amber-400 text-white">NEW</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $product->category->name ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono text-gray-400 text-xs">
                        <span class="editable cursor-text hover:bg-indigo-50 hover:text-indigo-700 rounded px-1 -mx-1 transition-colors"
                              data-product-id="{{ $product->id }}"
                              data-field="sku"
                              data-value="{{ $product->sku ?? '' }}"
                              data-type="text"
                              data-nullable="true"
                              title="Cliquer pour modifier le SKU">{{ $product->sku ?: '— SKU' }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-500">
                        <span class="editable cursor-text hover:bg-indigo-50 hover:text-indigo-700 rounded px-1 -mx-1 transition-colors"
                              data-product-id="{{ $product->id }}"
                              data-field="volume_ml"
                              data-value="{{ $product->volume_ml ?? '' }}"
                              data-type="integer"
                              data-nullable="true"
                              title="Cliquer pour modifier le volume (en ml)">
                            {{ $product->volume_ml ? $product->volume_ml . ' ml' : '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-900">
                        <span class="editable cursor-text hover:bg-indigo-50 hover:text-indigo-700 rounded px-1 -mx-1 transition-colors"
                              data-product-id="{{ $product->id }}"
                              data-field="purchase_price"
                              data-value="{{ $product->purchase_price }}"
                              data-type="price"
                              title="Cliquer pour modifier le prix d'achat">
                            {{ number_format($product->purchase_price, 2, ',', ' ') }} €
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <form method="POST" action="{{ route('fournisseur.products.toggle-new', $product) }}" class="inline">
                            @csrf
                            <button type="submit" title="{{ $product->is_new ? 'Retirer le badge NEW' : 'Marquer comme nouveauté' }}"
                                class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold transition-colors cursor-pointer
                                       {{ $product->is_new ? 'bg-amber-100 text-amber-700 hover:bg-gray-100 hover:text-gray-500' : 'bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-700' }}">
                                {{ $product->is_new ? '★ NEW' : '☆ —' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($product->is_active)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Actif</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">En attente</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center">
                        <form method="POST" action="{{ route('fournisseur.products.toggle-stock', $product) }}" class="inline">
                            @csrf
                            @if($product->in_stock)
                                <button type="submit"
                                    title="Marquer en rupture"
                                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 hover:bg-red-100 hover:text-red-700 transition-colors cursor-pointer">
                                    ✓ En stock
                                </button>
                            @else
                                <button type="submit"
                                    title="Marquer en stock"
                                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 hover:bg-emerald-100 hover:text-emerald-700 transition-colors cursor-pointer">
                                    ✗ Rupture
                                </button>
                            @endif
                        </form>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('fournisseur.products.edit', $product) }}"
                           class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                            Modifier →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-5 py-12 text-center text-gray-400">Aucun produit trouvé.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>

<script>
const QUICK_UPDATE_URL = '{{ url('fournisseur/produits') }}';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

function formatPrice(val) {
    if (val === null || val === '' || val === undefined) return '—';
    return parseFloat(val).toFixed(2).replace('.', ',') + ' €';
}

function showSaveState(el, state) {
    if (state === 'saving') {
        el.style.opacity = '0.5';
    } else if (state === 'saved') {
        el.style.opacity = '1';
        el.style.outline = '2px solid #10b981';
        setTimeout(() => { el.style.outline = ''; }, 1200);
    } else if (state === 'error') {
        el.style.opacity = '1';
        el.style.outline = '2px solid #ef4444';
        setTimeout(() => { el.style.outline = ''; }, 2000);
    }
}

function saveField(el, field, value) {
    const productId = el.dataset.productId;
    const url = `${QUICK_UPDATE_URL}/${productId}/quick-update`;
    showSaveState(el, 'saving');

    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ field, value }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showSaveState(el, 'saved');
            if (field === 'purchase_price') {
                el.textContent = formatPrice(data.value);
            } else if (field === 'volume_ml') {
                el.textContent = data.value ? data.value + ' ml' : '—';
            } else if (field === 'alcohol_degree') {
                el.textContent = data.value ? data.value + ' %' : '—';
            } else {
                el.textContent = data.value || (field === 'sku' ? '— SKU' : '—');
            }
            el.dataset.value = data.value ?? '';
        } else {
            showSaveState(el, 'error');
        }
    })
    .catch(() => showSaveState(el, 'error'));
}

function openEditor(el) {
    if (el.querySelector('input')) return;

    const field    = el.dataset.field;
    const value    = el.dataset.value;
    const type     = el.dataset.type;
    const nullable = el.dataset.nullable === 'true';

    const input = document.createElement('input');
    input.className = 'text-xs border border-indigo-400 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-indigo-500';

    if (type === 'price' || type === 'decimal') {
        input.type = 'number';
        input.step = '0.01';
        input.min = '0';
        input.style.width = '80px';
    } else if (type === 'integer') {
        input.type = 'number';
        input.step = '1';
        input.min = '0';
        input.style.width = '80px';
    } else {
        input.type = 'text';
        input.style.width = '120px';
    }
    input.value = value;

    const originalContent = el.innerHTML;
    el.innerHTML = '';
    el.appendChild(input);
    input.focus();
    if (input.select) input.select();

    const cancel = () => { el.innerHTML = originalContent; };

    const save = () => {
        const newVal = input.value.trim() === '' && nullable ? null : input.value.trim();
        if (String(newVal ?? '') === String(value ?? '')) { cancel(); return; }
        el.innerHTML = originalContent;
        saveField(el, field, newVal);
    };

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); save(); }
        if (e.key === 'Escape') { cancel(); }
    });
    input.addEventListener('blur', save);
}

document.querySelectorAll('.editable').forEach(el => {
    el.addEventListener('click', e => { e.stopPropagation(); openEditor(el); });
});

document.addEventListener('click', () => {
    document.querySelectorAll('.editable input').forEach(input => {
        const parent = input.closest('.editable');
        if (parent) input.dispatchEvent(new Event('blur'));
    });
});
</script>

{{-- Modal partage PDF --}}
<div id="share-pdf-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-900">📄 Catalogue PDF — Partager</h2>
            <button onclick="document.getElementById('share-pdf-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl font-bold leading-none">×</button>
        </div>

        <div class="space-y-3">
            <a href="{{ route('fournisseur.products.catalog-pdf') }}" target="_blank"
               class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors font-medium text-sm">
                <span class="text-lg">📥</span>
                <div>
                    <div>Télécharger le PDF</div>
                    <div class="text-xs opacity-70">Prix d'achat fournisseur</div>
                </div>
            </a>

            <button onclick="shareViaEmail()"
                    class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 transition-colors font-medium text-sm">
                <span class="text-lg">✉️</span>
                <div class="text-left">
                    <div>Partager par email</div>
                    <div class="text-xs opacity-70">Ouvre votre client mail</div>
                </div>
            </button>

            <button onclick="shareViaWhatsApp()"
                    class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 transition-colors font-medium text-sm">
                <span class="text-lg">💬</span>
                <div class="text-left">
                    <div>Partager via WhatsApp</div>
                    <div class="text-xs opacity-70">Lien vers le PDF en ligne</div>
                </div>
            </button>

            <button onclick="copyPdfLink()"
                    class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-700 hover:bg-gray-100 transition-colors font-medium text-sm">
                <span class="text-lg">🔗</span>
                <div class="text-left">
                    <div id="copy-btn-text">Copier le lien du PDF</div>
                    <div class="text-xs text-gray-400">Lien direct vers le catalogue</div>
                </div>
            </button>
        </div>
    </div>
</div>

<script>
const PDF_URL = '{{ route('fournisseur.products.catalog-pdf') }}';
const PDF_MESSAGE = 'Bonjour,\n\nVeuillez trouver ci-joint le catalogue La Tournée!.\n\nLien direct : ' + PDF_URL;

function shareViaEmail() {
    const subject = encodeURIComponent('Catalogue La Tournée!');
    const body = encodeURIComponent(PDF_MESSAGE);
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
}

function shareViaWhatsApp() {
    const text = encodeURIComponent('Catalogue La Tournée! : ' + PDF_URL);
    window.open(`https://wa.me/?text=${text}`, '_blank');
}

function copyPdfLink() {
    navigator.clipboard.writeText(PDF_URL).then(() => {
        const btn = document.getElementById('copy-btn-text');
        btn.textContent = '✓ Lien copié !';
        setTimeout(() => { btn.textContent = 'Copier le lien du PDF'; }, 2000);
    });
}
</script>
@endsection
