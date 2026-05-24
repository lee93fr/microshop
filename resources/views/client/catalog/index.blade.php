{{-- client/catalog/index.blade.php --}}
@extends('layouts.client')
@section('title', 'Catalogue')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Notre catalogue</h1>
    <p class="text-gray-500 mt-1">Sélectionnez vos produits et passez commande en quelques clics.</p>
</div>

@php $currentSearch = request('search'); @endphp

<style>
#cat-mobile  { display: block; }
#cat-desktop { display: none; }
@media (min-width: 768px) {
    #cat-mobile  { display: none; }
    #cat-desktop { display: block; }
}
</style>

{{-- Toggle catégories mobile --}}
<div id="cat-mobile" class="mb-4">
    <button id="cat-toggle" class="w-full flex items-center justify-between px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm font-medium text-gray-700">
        <span>Catégories{{ request('category') ? ' · ' . $categories->firstWhere('slug', request('category'))?->name : '' }}</span>
        <svg id="cat-chevron" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div id="cat-drawer" class="hidden mt-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <nav class="p-2 space-y-1">
            <a href="{{ route('catalog.index', $currentSearch ? ['search' => $currentSearch] : []) }}"
               class="block px-3 py-2 rounded-lg text-sm transition-colors {{ !request('category') ? 'bg-gray-900 text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                Tout voir
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('catalog.index', array_filter(['category' => $cat->slug, 'search' => $currentSearch])) }}"
               class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors {{ request('category') === $cat->slug ? 'bg-gray-900 text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <span>{{ $cat->name }}</span>
                <span class="text-xs opacity-60">{{ $cat->products_count }}</span>
            </a>
            @endforeach
        </nav>
    </div>
</div>

<div class="flex gap-8">
    {{-- Sidebar catégories desktop --}}
    <aside id="cat-desktop" class="w-48 shrink-0">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Catégories</h3>
        <nav class="space-y-1">
            <a href="{{ route('catalog.index', $currentSearch ? ['search' => $currentSearch] : []) }}"
               class="block px-3 py-1.5 rounded-lg text-sm transition-colors {{ !request('category') ? 'bg-gray-900 text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                Tout voir
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('catalog.index', array_filter(['category' => $cat->slug, 'search' => $currentSearch])) }}"
               class="flex items-center justify-between px-3 py-1.5 rounded-lg text-sm transition-colors {{ request('category') === $cat->slug ? 'bg-gray-900 text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <span>{{ $cat->name }}</span>
                <span class="text-xs opacity-60">{{ $cat->products_count }}</span>
            </a>
            @endforeach
        </nav>
    </aside>

    {{-- Contenu produits --}}
    <div class="flex-1 min-w-0">
        {{-- Barre de recherche + toggle vue --}}
        <div class="mb-6 flex gap-2 items-center min-w-0">
            <form method="GET" class="flex gap-2 flex-1 min-w-0">
                @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher…"
                       class="flex-1 min-w-0 rounded-xl border-gray-200 text-sm focus:ring-gray-900 focus:border-gray-900">
                <button type="submit" class="btn-primary rounded-xl px-3 sm:px-4 shrink-0" title="Rechercher" aria-label="Rechercher">
                    <span class="sm:hidden">🔍</span>
                    <span class="hidden sm:inline">Rechercher</span>
                </button>
                @if(request('search'))
                <a href="{{ route('catalog.index', ['category' => request('category')]) }}" class="btn-secondary rounded-xl shrink-0">✕</a>
                @endif
            </form>

            {{-- Toggle grille / liste --}}
            <div class="flex border border-gray-200 rounded-xl overflow-hidden shrink-0">
                <button id="btn-grid" onclick="setCatalogView('grid')"
                    class="p-2 transition-colors" title="Vue grille">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button id="btn-list" onclick="setCatalogView('list')"
                    class="p-2 transition-colors border-l border-gray-200" title="Vue liste">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>

        @if($products->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-5xl mb-3">🔍</div>
            <p>Aucun produit trouvé.</p>
        </div>
        @else

        {{-- VUE GRILLE --}}
        <div id="catalog-grid" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
            @foreach($products as $product)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
                <div class="relative">
                    @if($product->is_new)
                    <span class="absolute top-2 left-2 z-10 px-2 py-0.5 bg-amber-400 text-white text-xs font-bold rounded-full shadow-sm tracking-wide">NEW</span>
                    @endif
                    @auth
                    @php $qty = $cartItems[$product->id] ?? 0; @endphp
                    <span data-product-badge="{{ $product->id }}"
                          class="absolute top-2 right-2 z-10 h-6 w-6 bg-indigo-600 text-white text-xs font-bold rounded-full {{ $qty > 0 ? 'flex' : 'hidden' }} items-center justify-center ring-2 ring-white shadow">
                        {{ $qty > 0 ? $qty : '' }}
                    </span>
                    @endauth
                    <a href="{{ route('catalog.show', $product) }}" class="block aspect-square bg-gray-50 overflow-hidden">
                        @if($product->image)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-6xl">🍷</div>
                        @endif
                    </a>
                </div>

                <div class="p-4">
                    <p class="text-xs text-gray-400 mb-0.5">{{ $product->category->name }}</p>
                    <a href="{{ route('catalog.show', $product) }}">
                        <h3 class="font-semibold text-gray-900 text-sm leading-tight hover:text-indigo-600 transition-colors line-clamp-2">
                            {{ $product->name }}
                        </h3>
                    </a>
                    @if($product->volume_ml || $product->alcohol_degree)
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $product->volume_ml ? $product->volume_ml . ' ml' : '' }}
                        {{ $product->alcohol_degree ? '· ' . $product->alcohol_degree . '°' : '' }}
                    </p>
                    @endif

                    <div class="mt-3">
                        @if(!$product->in_stock)
                        <span class="inline-block text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg px-2 py-0.5 mb-2">Rupture de stock</span>
                        @endif
                        <div class="flex items-center justify-between gap-2">
                            <div class="shrink-0">
                                @if($product->suggested_price && $product->suggested_price > $product->sale_price)
                                <span class="block text-xs text-gray-400 line-through leading-none mb-0.5">{{ number_format($product->suggested_price, 2, ',', ' ') }} €</span>
                                @endif
                                <span class="font-bold {{ $product->in_stock ? 'text-gray-900' : 'text-gray-400' }}">{{ number_format($product->sale_price, 2, ',', ' ') }} €</span>
                            </div>
                            @auth
                            @if($product->in_stock)
                            @php $cartQty = $cartItems[$product->id] ?? 0; @endphp
                            <div data-cart-stepper data-product-id="{{ $product->id }}" class="flex items-center gap-1">
                                <button type="button" data-cart-dec @disabled($cartQty <= 0)
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full border border-gray-200 text-gray-700 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed font-bold leading-none shrink-0">−</button>
                                <input type="number" data-cart-qty value="{{ $cartQty }}" min="0" max="999" aria-label="Quantité au panier"
                                       class="w-11 rounded-lg border-gray-200 text-center text-xs font-medium py-1 px-1">
                                <button type="button" data-cart-inc
                                    class="inline-flex items-center justify-center w-7 h-7 bg-gray-900 text-white rounded-full hover:bg-indigo-600 transition-colors font-bold text-base leading-none shrink-0">+</button>
                            </div>
                            @else
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-300 rounded-full font-bold text-lg leading-none cursor-not-allowed" title="Rupture de stock">+</span>
                            @endif
                            @else
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-400 rounded-full hover:bg-gray-200 transition-colors font-bold text-lg leading-none"
                               title="Connectez-vous pour ajouter au panier">+</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- VUE LISTE --}}

        <div id="catalog-list" class="hidden space-y-3">
            @foreach($products as $product)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 p-4">
                    <div class="flex items-center gap-4 min-w-0">
                    <div class="relative shrink-0">
                        @if($product->is_new)
                        <span class="absolute -top-1.5 -left-1.5 z-10 px-1.5 py-0.5 bg-amber-400 text-white text-xs font-bold rounded-full shadow-sm">NEW</span>
                        @endif
                        @auth
                        @php $qty = $cartItems[$product->id] ?? 0; @endphp
                        <span data-product-badge="{{ $product->id }}"
                              class="absolute -top-1.5 -right-1.5 z-10 h-5 w-5 bg-indigo-600 text-white text-xs font-bold rounded-full {{ $qty > 0 ? 'flex' : 'hidden' }} items-center justify-center ring-2 ring-white shadow">
                            {{ $qty > 0 ? $qty : '' }}
                        </span>
                        @endauth
                        <a href="{{ route('catalog.show', $product) }}" class="block h-20 w-20 rounded-xl bg-gray-50 overflow-hidden">
                            @if($product->image)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-3xl">🍷</div>
                            @endif
                        </a>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-400 mb-0.5">{{ $product->category->name }}</p>
                        <a href="{{ route('catalog.show', $product) }}">
                            <h3 class="font-semibold text-gray-900 hover:text-indigo-600 transition-colors truncate">{{ $product->name }}</h3>
                        </a>
                        @if($product->volume_ml || $product->alcohol_degree)
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $product->volume_ml ? $product->volume_ml . ' ml' : '' }}
                            {{ $product->alcohol_degree ? '· ' . $product->alcohol_degree . '°' : '' }}
                        </p>
                        @endif
                        @if($product->description)
                        <p class="text-sm text-gray-500 mt-1 line-clamp-1">{{ $product->description }}</p>
                        @endif
                    </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3 sm:shrink-0">
                        <div class="text-left sm:text-right">
                            @if($product->suggested_price && $product->suggested_price > $product->sale_price)
                            <span class="block text-xs text-gray-400 line-through leading-none mb-0.5">{{ number_format($product->suggested_price, 2, ',', ' ') }} €</span>
                            @endif
                            <span class="font-bold text-gray-900 text-lg whitespace-nowrap">{{ number_format($product->sale_price, 2, ',', ' ') }} €</span>
                        </div>
                        @auth
                        @if($product->in_stock)
                        @php $cartQty = $cartItems[$product->id] ?? 0; @endphp
                        <div data-cart-stepper data-product-id="{{ $product->id }}" class="flex items-center gap-1.5">
                            <button type="button" data-cart-dec @disabled($cartQty <= 0)
                                class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed font-bold leading-none shrink-0">−</button>
                            <input type="number" data-cart-qty value="{{ $cartQty }}" min="0" max="999" aria-label="Quantité au panier"
                                   class="w-14 rounded-xl border-gray-200 text-center text-sm font-medium">
                            <button type="button" data-cart-inc
                                class="inline-flex items-center justify-center w-9 h-9 bg-gray-900 text-white rounded-xl hover:bg-indigo-600 transition-colors font-bold leading-none shrink-0">+</button>
                            <button type="button" data-cart-reset title="Retirer du panier"
                                class="ml-1 text-gray-300 hover:text-red-500 transition-colors text-sm {{ $cartQty > 0 ? '' : 'hidden' }}">✕</button>
                        </div>
                        @else
                        <span class="px-4 py-2 bg-red-50 text-red-400 border border-red-200 rounded-xl text-sm font-medium whitespace-nowrap">Rupture</span>
                        @endif
                        @else
                        <a href="{{ route('login') }}" class="px-4 py-2 bg-gray-100 text-gray-400 rounded-xl text-sm font-medium">
                            Se connecter
                        </a>
                        @endauth
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </div>
</div>

<script>
function setCatalogView(view) {
    var grid    = document.getElementById('catalog-grid');
    var list    = document.getElementById('catalog-list');
    var btnGrid = document.getElementById('btn-grid');
    var btnList = document.getElementById('btn-list');
    if (!grid || !list) return;

    if (view === 'list') {
        grid.classList.add('hidden');
        list.classList.remove('hidden');
        btnGrid.classList.remove('bg-gray-900', 'text-white');
        btnGrid.classList.add('text-gray-400');
        btnList.classList.add('bg-gray-900', 'text-white');
        btnList.classList.remove('text-gray-400');
    } else {
        list.classList.add('hidden');
        grid.classList.remove('hidden');
        btnList.classList.remove('bg-gray-900', 'text-white');
        btnList.classList.add('text-gray-400');
        btnGrid.classList.add('bg-gray-900', 'text-white');
        btnGrid.classList.remove('text-gray-400');
    }
    localStorage.setItem('catalogView', view);
}

document.addEventListener('DOMContentLoaded', function () {
    var saved = localStorage.getItem('catalogView') || 'grid';
    setCatalogView(saved);

    var catBtn = document.getElementById('cat-toggle');
    var catDrawer = document.getElementById('cat-drawer');
    var catChevron = document.getElementById('cat-chevron');
    if (catBtn) {
        catBtn.addEventListener('click', function() {
            var hidden = catDrawer.classList.toggle('hidden');
            catChevron.style.transform = hidden ? '' : 'rotate(180deg)';
        });
    }
});
</script>
@endsection
