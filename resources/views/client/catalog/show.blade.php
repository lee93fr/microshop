@extends('layouts.client')
@section('title', $product->name)

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}"
       class="text-sm text-gray-400 hover:text-gray-600 mb-6 inline-block">← {{ $product->category->name }}</a>

    <div class="flex flex-col md:flex-row gap-6 items-start">

        {{-- Bloc image --}}
        <div class="w-full md:w-72 md:flex-shrink-0 rounded-2xl overflow-hidden bg-gray-50 border border-gray-100 flex items-center justify-center" style="max-height: 420px; min-height: 280px;">
            @if($product->image)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-contain" style="max-height: 420px;">
            @else
                <div class="flex items-center justify-center text-8xl p-12">🍷</div>
            @endif
        </div>

        {{-- Bloc infos + panier --}}
        <div class="flex-1 bg-white border border-gray-100 rounded-2xl p-6 flex flex-col gap-6">

            {{-- En-tête --}}
            <div>
                <p class="text-sm text-gray-400 mb-1">{{ $product->category->name }}</p>
                <div class="flex items-center gap-2 mb-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                    @if($product->is_new)
                    <span class="inline-flex px-2 py-0.5 bg-amber-400 text-white text-xs font-bold rounded-full flex-shrink-0">NEW</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($product->volume_ml)
                    <span class="badge-gray">🍾 {{ $product->volume_ml }} ml</span>
                    @endif
                    @if($product->alcohol_degree)
                    <span class="badge-gray">🔥 {{ $product->alcohol_degree }}°</span>
                    @endif
                    <span class="badge-gray">📦 {{ ucfirst($product->unit) }}</span>
                </div>
            </div>

            {{-- Description --}}
            @if($product->description)
            <p class="text-gray-600 text-sm leading-relaxed">{{ $product->description }}</p>
            @endif

            {{-- Prix + action --}}
            <div class="mt-auto border-t border-gray-100 pt-6">
                <p class="text-3xl font-bold {{ $product->in_stock ? 'text-gray-900' : 'text-gray-400' }} mb-4">
                    {{ number_format($product->sale_price, 2, ',', ' ') }} €
                </p>

                @if(!$product->in_stock)
                <div class="flex items-center gap-2 mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium">
                    ⚠️ Ce produit est actuellement en rupture de stock.
                </div>
                @endif

                @auth
                @if($product->in_stock)
                <div data-cart-stepper data-product-id="{{ $product->id }}" class="flex items-center gap-2">
                    <button type="button" data-cart-dec @disabled($cartQty <= 0)
                        class="inline-flex items-center justify-center w-11 h-11 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed font-bold text-lg leading-none shrink-0">−</button>
                    <input type="number" data-cart-qty value="{{ $cartQty }}" min="0" max="999" aria-label="Quantité au panier"
                           class="w-20 rounded-xl border-gray-300 text-center font-medium py-2.5">
                    <button type="button" data-cart-inc
                        class="flex-1 inline-flex items-center justify-center gap-2 btn-primary justify-center py-3 rounded-xl text-base">
                        🛒 Ajouter au panier
                    </button>
                    <button type="button" data-cart-reset title="Vider"
                        class="inline-flex items-center justify-center w-11 h-11 rounded-xl border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-200 transition-colors {{ $cartQty > 0 ? '' : 'hidden' }}">✕</button>
                </div>
                @else
                <button disabled class="w-full py-3 rounded-xl text-base font-semibold bg-gray-100 text-gray-400 cursor-not-allowed">
                    Rupture de stock
                </button>
                @endif
                @else
                <a href="{{ route('login') }}"
                   class="block w-full btn-primary justify-center py-3 rounded-xl text-base text-center">
                    Connectez-vous pour commander
                </a>
                @endauth
            </div>
        </div>

    </div>
</div>
@endsection
