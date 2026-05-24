@extends('layouts.client')
@section('title', 'Mon panier')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Mon panier</h1>

@if($items->isEmpty())
<div class="text-center py-20 text-gray-400">
    <div class="text-6xl mb-4">🛒</div>
    <p class="text-lg mb-6">Votre panier est vide.</p>
    <a href="{{ route('catalog.index') }}" class="btn-primary px-8 py-3 rounded-xl">Voir le catalogue</a>
</div>
@else
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
    <div class="lg:col-span-2 space-y-3">
        @foreach($items as $item)
        <div class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-4 flex items-center gap-3 sm:gap-4">
            <a href="{{ route('catalog.show', $item->product) }}" class="shrink-0">
                <div class="h-16 w-16 rounded-xl bg-gray-50 overflow-hidden">
                    @if($item->product->image)
                        <img src="{{ $item->product->image_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-2xl">🍷</div>
                    @endif
                </div>
            </a>

            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-gray-900 truncate">{{ $item->product->name }}</h3>
                <p class="text-sm text-gray-400">{{ $item->product->category->name }}</p>
            </div>

            <form method="POST" action="{{ route('client.cart.update', $item->product_id) }}" class="flex items-center gap-2">
                @csrf @method('PATCH')
                <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="999"
                       class="w-16 rounded-lg border-gray-200 text-center text-sm font-medium"
                       onchange="this.form.submit()">
            </form>

            <div class="text-right shrink-0">
                <div class="font-bold text-gray-900">{{ number_format($item->product->sale_price * $item->quantity, 2, ',', ' ') }} €</div>
                <div class="text-xs text-gray-400">{{ number_format($item->product->sale_price, 2, ',', ' ') }} € / unité</div>
            </div>

            <form method="POST" action="{{ route('client.cart.remove', $item->product_id) }}">
                @csrf @method('DELETE')
                <button class="text-gray-300 hover:text-red-500 transition-colors p-1 text-lg leading-none">×</button>
            </form>
        </div>
        @endforeach

        <div class="flex justify-end pt-1">
            <form method="POST" action="{{ route('client.cart.clear') }}">
                @csrf @method('DELETE')
                <button
                    onclick="return confirm('Vider le panier ? Tous les articles seront supprimés.')"
                    class="text-sm text-gray-400 hover:text-red-500 transition-colors">
                    Vider le panier
                </button>
            </form>
        </div>
    </div>

    {{-- Résumé --}}
    <div>
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
            <h2 class="font-semibold text-gray-900 mb-4">Récapitulatif</h2>
            <div class="space-y-2 text-sm mb-4">
                @foreach($items as $item)
                <div class="flex justify-between text-gray-600">
                    <span class="truncate mr-2">{{ $item->product->name }} × {{ $item->quantity }}</span>
                    <span class="shrink-0 font-medium">{{ number_format($item->product->sale_price * $item->quantity, 2, ',', ' ') }} €</span>
                </div>
                @endforeach
            </div>

            {{-- Code promo --}}
            <div class="pt-4 border-t border-gray-100 mb-4">
                @if($promo)
                <div class="flex items-center justify-between p-3 rounded-xl bg-green-50 border border-green-200">
                    <div>
                        <div class="text-sm font-semibold text-green-800">🎁 Code « {{ $promo->code }} »</div>
                        <div class="text-xs text-green-700">−{{ number_format($discount, 2, ',', ' ') }} € appliqué</div>
                    </div>
                    <form method="POST" action="{{ route('client.cart.promo.remove') }}">
                        @csrf @method('DELETE')
                        <button class="text-xs text-green-700 hover:text-red-600 underline">Retirer</button>
                    </form>
                </div>
                @else
                <form method="POST" action="{{ route('client.cart.promo.apply') }}" class="space-y-1">
                    @csrf
                    <label class="form-label text-xs">Code promo</label>
                    <div class="flex gap-2">
                        <input type="text" name="promo_code" value="{{ old('promo_code') }}"
                               placeholder="ex. WELCOME10"
                               class="form-input flex-1 @error('promo_code') border-red-500 @enderror">
                        <button class="btn-primary px-4 rounded-xl text-sm">Appliquer</button>
                    </div>
                    @error('promo_code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </form>
                @endif
            </div>

            <div class="pt-3 border-t border-gray-100 space-y-1 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>Sous-total</span>
                    <span>{{ number_format($total, 2, ',', ' ') }} €</span>
                </div>
                @if($promo)
                <div class="flex justify-between text-green-700">
                    <span>Remise</span>
                    <span>−{{ number_format($discount, 2, ',', ' ') }} €</span>
                </div>
                @endif
            </div>
            <div class="pt-3 border-t border-gray-100 flex justify-between text-lg font-bold text-gray-900 mb-5 mt-2">
                <span>Total</span>
                <span>{{ number_format(max(0, $total - $discount), 2, ',', ' ') }} €</span>
            </div>
            <a href="{{ route('client.checkout') }}"
               class="btn-primary w-full justify-center py-3 rounded-xl text-base">
                Commander →
            </a>
        </div>
    </div>
</div>
@endif
@endsection
