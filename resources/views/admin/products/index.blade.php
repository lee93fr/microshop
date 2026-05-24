@extends('layouts.admin')
@section('title', 'Produits')
@section('header', 'Catalogue produits')

@section('header-actions')
    <a href="{{ route('admin.products.import.form') }}" class="btn-secondary gap-2">📥 Importer CSV</a>
    <button onclick="document.getElementById('share-pdf-modal').classList.remove('hidden')"
            class="btn-secondary gap-2">📄 PDF catalogue</button>
    <a href="{{ route('admin.produits.create') }}" class="btn-primary gap-2">+ Nouveau produit</a>
@endsection

@section('content')

@php
    $sortUrl  = fn($col) => request()->fullUrlWithQuery([
        'sort'      => $col,
        'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc',
        'page'      => 1,
    ]);
    $sortIcon = fn($col) => $sort !== $col
        ? '<span class="text-gray-300 text-xs ml-1">⇅</span>'
        : ($direction === 'asc'
            ? '<span class="text-indigo-500 text-xs ml-1">↑</span>'
            : '<span class="text-indigo-500 text-xs ml-1">↓</span>');
@endphp

{{-- Toolbar --}}
<div class="flex items-center justify-between mb-3">
    <span class="text-sm text-gray-500">
        {{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}
        @if(request()->hasAny(['search','category_id','is_active']))
            <a href="{{ route('admin.produits.index') }}" class="ml-2 text-xs text-indigo-600 hover:underline">✕ Réinitialiser les filtres</a>
        @endif
    </span>
    <a href="{{ route('admin.products.export') }}" class="text-xs text-green-700 hover:underline">📥 Exporter CSV</a>
</div>

{{-- Table --}}
<div class="card overflow-hidden overflow-x-auto">
    <form method="GET" id="filter-form">
        {{-- Preserve sort state --}}
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">

        <table class="min-w-full divide-y divide-gray-200 text-sm" id="products-table">
            <thead>
                {{-- Sort headers --}}
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left">
                        <a href="{{ $sortUrl('name') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                            Produit {!! $sortIcon('name') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Catégorie</th>
                    <th class="px-6 py-3 text-left">
                        <a href="{{ $sortUrl('volume_ml') }}" class="inline-flex items-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                            Contenance {!! $sortIcon('volume_ml') !!}
                        </a>
                    </th>
                    <th class="col-purchase px-6 py-3 text-right">
                        <a href="{{ $sortUrl('purchase_price') }}" class="inline-flex items-center justify-end text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                            Prix achat {!! $sortIcon('purchase_price') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-right">
                        <a href="{{ $sortUrl('sale_price') }}" class="inline-flex items-center justify-end text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                            Prix vente {!! $sortIcon('sale_price') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Prix conseillé</th>
                    <th class="col-margin px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Marge</th>
                    <th class="px-6 py-3 text-center">
                        <a href="{{ $sortUrl('is_active') }}" class="inline-flex items-center justify-center text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-indigo-600">
                            Statut {!! $sortIcon('is_active') !!}
                        </a>
                    </th>
                    <th class="px-6 py-3"></th>
                </tr>
                {{-- Filter row --}}
                <tr class="bg-white border-b border-gray-100">
                    <td class="px-4 py-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Nom ou SKU…"
                               class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500"
                               oninput="debounceFilter()">
                    </td>
                    <td class="px-4 py-2">
                        <select name="category_id" onchange="document.getElementById('filter-form').submit()"
                                class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Toutes</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-2">
                        <input type="number" name="volume_min" value="{{ request('volume_min') }}"
                               placeholder="min ml"
                               class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500"
                               oninput="debounceFilter()">
                    </td>
                    <td class="col-purchase px-4 py-2">
                        <input type="number" name="price_min" value="{{ request('price_min') }}"
                               placeholder="min €" step="0.01"
                               class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500"
                               oninput="debounceFilter()">
                    </td>
                    <td class="px-4 py-2">
                        <input type="number" name="sale_min" value="{{ request('sale_min') }}"
                               placeholder="min €" step="0.01"
                               class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500"
                               oninput="debounceFilter()">
                    </td>
                    <td class="px-4 py-2"></td>
                    <td class="col-margin px-4 py-2"></td>
                    <td class="px-4 py-2">
                        <select name="is_active" onchange="document.getElementById('filter-form').submit()"
                                class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Tous</option>
                            <option value="1" @selected(request('is_active') === '1')>Actif</option>
                            <option value="0" @selected(request('is_active') === '0')>Inactif</option>
                        </select>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <button type="submit" class="text-xs text-indigo-600 hover:underline font-medium">Filtrer</button>
                    </td>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($products as $product)
                <tr id="row-{{ $product->id }}" class="hover:bg-gray-50">
                    {{-- Produit --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($product->image)
                                <div class="h-10 w-10 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                    <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover">
                                </div>
                            @else
                                <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xl flex-shrink-0">🍷</div>
                            @endif
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <div class="font-medium text-gray-900 editable cursor-text hover:bg-indigo-50 hover:text-indigo-700 rounded px-1 -mx-1 transition-colors"
                                         data-product-id="{{ $product->id }}"
                                         data-field="name"
                                         data-value="{{ $product->name }}"
                                         data-type="text"
                                         title="Cliquer pour modifier">{{ $product->name }}</div>
                                    @if($product->is_new)
                                    <span class="inline-flex px-1.5 py-0.5 bg-amber-400 text-white text-xs font-bold rounded-full leading-none flex-shrink-0">NEW</span>
                                    @endif
                                </div>
                                <div class="text-gray-400 text-xs font-mono mt-0.5 editable cursor-text hover:bg-indigo-50 hover:text-indigo-600 rounded px-1 -mx-1 transition-colors"
                                     data-product-id="{{ $product->id }}"
                                     data-field="sku"
                                     data-value="{{ $product->sku ?? '' }}"
                                     data-type="text"
                                     data-nullable="true"
                                     title="Cliquer pour modifier le SKU">{{ $product->sku ?: '— SKU' }}</div>
                            </div>
                        </div>
                    </td>
                    {{-- Catégorie --}}
                    <td class="px-6 py-4">
                        <span class="editable cursor-text hover:bg-indigo-50 hover:text-indigo-700 rounded px-1 -mx-1 transition-colors"
                              data-product-id="{{ $product->id }}"
                              data-field="category_id"
                              data-value="{{ $product->category_id }}"
                              data-type="select"
                              data-display="{{ $product->category->name }}"
                              title="Cliquer pour modifier">{{ $product->category->name }}</span>
                    </td>
                    {{-- Contenance --}}
                    <td class="px-6 py-4 text-gray-500">
                        <span class="editable cursor-text hover:bg-indigo-50 hover:text-indigo-700 rounded px-1 -mx-1 transition-colors"
                              data-product-id="{{ $product->id }}"
                              data-field="volume_ml"
                              data-value="{{ $product->volume_ml ?? '' }}"
                              data-type="integer"
                              data-nullable="true"
                              data-display-format="volume"
                              title="Cliquer pour modifier (en ml)">
                            @if($product->volume_ml)
                                {{ $product->volume_ml >= 1000
                                    ? number_format($product->volume_ml / 1000, $product->volume_ml % 1000 === 0 ? 0 : 1, ',', '') . ' L'
                                    : $product->volume_ml . ' ml' }}
                            @else
                                —
                            @endif
                        </span>
                    </td>
                    {{-- Prix achat --}}
                    <td class="col-purchase px-6 py-4 text-right">
                        <span class="editable cursor-text hover:bg-indigo-50 hover:text-indigo-700 rounded px-1 -mx-1 transition-colors"
                              data-product-id="{{ $product->id }}"
                              data-field="purchase_price"
                              data-value="{{ $product->purchase_price }}"
                              data-type="price"
                              title="Cliquer pour modifier">{{ number_format($product->purchase_price, 2, ',', ' ') }} €</span>
                    </td>
                    {{-- Prix vente --}}
                    <td class="px-6 py-4 text-right">
                        <span class="editable font-semibold text-gray-900 cursor-text hover:bg-indigo-50 hover:text-indigo-700 rounded px-1 -mx-1 transition-colors"
                              data-product-id="{{ $product->id }}"
                              data-field="sale_price"
                              data-value="{{ $product->sale_price }}"
                              data-type="price"
                              title="Cliquer pour modifier">{{ number_format($product->sale_price, 2, ',', ' ') }} €</span>
                    </td>
                    {{-- Prix conseillé --}}
                    <td class="px-6 py-4 text-right text-gray-400">
                        <span class="editable cursor-text hover:bg-indigo-50 hover:text-indigo-600 rounded px-1 -mx-1 transition-colors {{ $product->suggested_price ? 'line-through' : '' }}"
                              data-product-id="{{ $product->id }}"
                              data-field="suggested_price"
                              data-value="{{ $product->suggested_price ?? '' }}"
                              data-type="price"
                              data-nullable="true"
                              title="Cliquer pour modifier">
                            @if($product->suggested_price)
                                {{ number_format($product->suggested_price, 2, ',', ' ') }} €
                            @else
                                —
                            @endif
                        </span>
                    </td>
                    {{-- Marge --}}
                    <td class="col-margin px-6 py-4 text-right">
                        <span id="margin-{{ $product->id }}"
                              class="{{ $product->margin >= 30 ? 'badge-green' : ($product->margin >= 15 ? 'badge-yellow' : 'badge-red') }}">
                            {{ $product->margin }}%
                        </span>
                    </td>
                    {{-- Statut --}}
                    <td class="px-6 py-4 text-center">
                        <form method="POST" action="{{ route('admin.products.toggle-active', $product) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="cursor-pointer {{ $product->is_active ? 'badge-green' : 'badge-gray' }}">
                                {{ $product->is_active ? '● Actif' : '○ Inactif' }}
                            </button>
                        </form>
                    </td>
                    {{-- Actions --}}
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.produits.edit', $product) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Modifier</a>
                            <form method="POST" action="{{ route('admin.produits.destroy', $product) }}"
                                  onsubmit="return confirm('Supprimer ce produit ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 text-sm">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-6 py-16 text-center text-gray-400">Aucun produit trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </form>
    <div class="px-6 py-4 border-t border-gray-100">{{ $products->links() }}</div>
</div>

<script>
// ── Filtre auto (debounce) ───────────────────────────────────
let filterTimer;
function debounceFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => document.getElementById('filter-form').submit(), 450);
}

// ── Inline editing ───────────────────────────────────────────
const CATEGORIES = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));
const QUICK_UPDATE_URL = '{{ url('admin/produits') }}';
const CSRF = '{{ csrf_token() }}';

function formatVolume(ml) {
    if (!ml) return '—';
    ml = parseInt(ml);
    if (ml >= 1000) {
        const l = ml / 1000;
        return (l % 1 === 0 ? l.toFixed(0) : l.toFixed(1)).replace('.', ',') + ' L';
    }
    return ml + ' ml';
}

function formatPrice(val) {
    if (val === null || val === '' || val === undefined) return '—';
    return parseFloat(val).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' €';
}

function showSaveState(el, state) {
    el.dataset.saving = state;
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

            // Update display value
            if (field === 'category_id') {
                el.textContent = data.category_name;
                el.dataset.value = value;
                el.dataset.display = data.category_name;
            } else if (field === 'volume_ml') {
                el.textContent = formatVolume(data.value);
                el.dataset.value = data.value ?? '';
            } else if (['purchase_price', 'sale_price', 'suggested_price'].includes(field)) {
                el.textContent = data.value ? formatPrice(data.value) : '—';
                if (field === 'suggested_price') {
                    el.classList.toggle('line-through', !!data.value);
                }
                el.dataset.value = data.value ?? '';
                // Update margin badge
                const badge = document.getElementById('margin-' + productId);
                if (badge) {
                    badge.textContent = data.margin + '%';
                    badge.className = data.margin_class;
                }
            } else {
                el.textContent = data.value || (field === 'sku' ? '— SKU' : '');
                el.dataset.value = data.value ?? '';
            }
        } else {
            showSaveState(el, 'error');
            const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Erreur');
            el.title = msg;
        }
    })
    .catch(() => showSaveState(el, 'error'));
}

function openEditor(el) {
    if (el.querySelector('input, select')) return; // already open

    const field    = el.dataset.field;
    const value    = el.dataset.value;
    const type     = el.dataset.type;
    const nullable = el.dataset.nullable === 'true';

    let input;

    if (type === 'select') {
        input = document.createElement('select');
        input.className = 'text-xs border border-indigo-400 rounded px-1 py-0.5 w-full focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white';
        if (nullable) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '—';
            input.appendChild(opt);
        }
        CATEGORIES.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            if (String(cat.id) === String(value)) opt.selected = true;
            input.appendChild(opt);
        });
    } else {
        input = document.createElement('input');
        input.className = 'text-xs border border-indigo-400 rounded px-1 py-0.5 w-full focus:outline-none focus:ring-1 focus:ring-indigo-500';
        if (type === 'price') {
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
        }
        input.value = value;
    }

    const originalContent = el.innerHTML;
    el.innerHTML = '';
    el.appendChild(input);
    input.focus();
    if (input.select) input.select();

    const cancel = () => {
        el.innerHTML = originalContent;
    };

    const save = () => {
        const newVal = input.value.trim() === '' && nullable ? null : input.value.trim();
        // No change → cancel
        if (String(newVal ?? '') === String(value ?? '')) { cancel(); return; }
        el.innerHTML = originalContent; // restore text first
        saveField(el, field, newVal);
    };

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); save(); }
        if (e.key === 'Escape') { cancel(); }
    });

    if (type === 'select') {
        input.addEventListener('change', save);
    } else {
        input.addEventListener('blur', save);
    }
}

// Attach click listeners
document.querySelectorAll('.editable').forEach(el => {
    el.addEventListener('click', e => {
        e.stopPropagation();
        openEditor(el);
    });
});

// Close editor if clicking outside
document.addEventListener('click', () => {
    document.querySelectorAll('.editable input, .editable select').forEach(input => {
        const parent = input.closest('.editable');
        if (parent) {
            const cancel = new Event('blur');
            input.dispatchEvent(cancel);
        }
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
            <a href="{{ route('admin.products.catalog-pdf') }}" target="_blank"
               class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-gray-900 text-white hover:bg-indigo-700 transition-colors font-medium text-sm">
                <span class="text-lg">📥</span>
                <div>
                    <div>Télécharger le PDF</div>
                    <div class="text-xs opacity-70">Prix de vente</div>
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
const PDF_URL = '{{ route('admin.products.catalog-pdf') }}';
const PDF_MESSAGE = 'Bonjour,\n\nVeuillez trouver ci-joint le catalogue La Tournée! avec les prix de vente.\n\nLien direct : ' + PDF_URL;

function shareViaEmail() {
    const subject = encodeURIComponent('Catalogue La Tournée! — Prix de vente');
    const body = encodeURIComponent(PDF_MESSAGE);
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
}

function shareViaWhatsApp() {
    const text = encodeURIComponent('Catalogue La Tournée! (prix de vente) : ' + PDF_URL);
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
