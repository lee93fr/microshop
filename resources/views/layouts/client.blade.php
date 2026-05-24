<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Boutique') — La Tournée!</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-stone-50 text-gray-900 font-sans antialiased">

{{-- ========== NAVBAR ========== --}}
<style>
#nav-desktop { display: none; }
#nav-mobile  { display: flex; }
@media (min-width: 768px) {
    #nav-desktop { display: flex; }
    #nav-mobile  { display: none; }
}
</style>
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm">

    {{-- Barre desktop --}}
    <div id="nav-desktop" class="max-w-7xl mx-auto px-6 h-16 items-center justify-between gap-6">
        <a href="{{ route('catalog.index') }}" class="flex items-center gap-2 shrink-0">
            <img src="/images/logo-la-tournee.svg" alt="La Tournée" class="h-9 w-9">
            <span class="text-xl font-bold tracking-tight text-gray-900">La Tournée!</span>
        </a>

        <nav class="flex items-center gap-6 text-sm font-medium text-gray-600">
            <a href="{{ route('catalog.index') }}" class="hover:text-gray-900 transition-colors {{ request()->routeIs('catalog*') ? 'text-gray-900 font-semibold' : '' }}">
                Catalogue
            </a>
            @auth
                <a href="{{ route('client.orders.index') }}" class="hover:text-gray-900 transition-colors {{ request()->routeIs('client.orders*') ? 'text-gray-900 font-semibold' : '' }}">Mes commandes</a>
                <a href="{{ route('client.profile.edit') }}" class="hover:text-gray-900 transition-colors {{ request()->routeIs('client.profile*') ? 'text-gray-900 font-semibold' : '' }}">Profil</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-semibold hover:text-indigo-800">Admin →</a>
                @endif
            @endauth
        </nav>

        <div class="flex items-center gap-3 shrink-0">
            @auth
            @php
                $cartService = app(\App\Services\CartService::class);
                $cartCount   = $cartService->count();
                $cartTotal   = $cartService->total();
            @endphp
            <a href="{{ route('client.cart') }}"
               class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-gray-200 text-sm font-medium hover:bg-gray-50 transition-colors">
                🛒 Panier
                <span id="cart-count" data-cart-count
                      class="{{ $cartCount > 0 ? 'inline-flex' : 'hidden' }} items-center justify-center h-5 w-5 rounded-full bg-indigo-600 text-white text-xs font-bold">{{ $cartCount > 0 ? $cartCount : '' }}</span>
                <span data-cart-total
                      class="{{ $cartTotal > 0 ? '' : 'hidden' }} pl-2 ml-1 border-l border-gray-200 font-semibold text-gray-900 whitespace-nowrap">{{ number_format($cartTotal, 2, ',', ' ') }} €</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button class="text-sm text-gray-400 hover:text-gray-700 transition-colors">Déconnexion</button>
            </form>
            @else
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Connexion</a>
            <a href="{{ route('register') }}" class="px-4 py-2 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-indigo-600 transition-colors">S'inscrire</a>
            @endauth
        </div>
    </div>

    {{-- ========== BARRE MOBILE (< md) ========== --}}
    <div id="nav-mobile" class="px-4 h-14 items-center justify-between gap-3">
        <a href="{{ route('catalog.index') }}" class="flex items-center gap-2 shrink-0">
            <img src="/images/logo-la-tournee.svg" alt="La Tournée" class="h-8 w-8">
            <span class="text-lg font-bold tracking-tight text-gray-900">La Tournée!</span>
        </a>

        <div class="flex items-center gap-2">
            @auth
            @php
                $cartService = $cartService ?? app(\App\Services\CartService::class);
                $cartCount   = $cartCount ?? $cartService->count();
                $cartTotal   = $cartTotal ?? $cartService->total();
            @endphp
            <a href="{{ route('client.cart') }}"
               class="relative inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 text-sm font-medium hover:bg-gray-50 transition-colors">
                🛒
                <span data-cart-count
                      class="{{ $cartCount > 0 ? 'inline-flex' : 'hidden' }} items-center justify-center h-5 w-5 rounded-full bg-indigo-600 text-white text-xs font-bold">{{ $cartCount > 0 ? $cartCount : '' }}</span>
                <span data-cart-total
                      class="{{ $cartTotal > 0 ? '' : 'hidden' }} text-xs font-semibold text-gray-900 whitespace-nowrap">{{ number_format($cartTotal, 2, ',', ' ') }} €</span>
            </a>
            @endauth

            <button id="mobile-menu-btn" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors" aria-label="Menu">
                <svg id="icon-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Menu mobile déroulant --}}
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white px-4 py-3 space-y-1">
        <a href="{{ route('catalog.index') }}"
           class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('catalog*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Catalogue
        </a>
        @auth
            <a href="{{ route('client.orders.index') }}"
               class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('client.orders*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
               Mes commandes
            </a>
            <a href="{{ route('client.profile.edit') }}"
               class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('client.profile*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
               Profil
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-indigo-600 hover:bg-indigo-50 transition-colors">Admin →</a>
            @endif
            <div class="pt-2 border-t border-gray-100 mt-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full text-left px-3 py-2.5 rounded-lg text-sm text-gray-500 hover:text-red-500 hover:bg-gray-50 transition-colors">Déconnexion</button>
                </form>
            </div>
        @else
            <a href="{{ route('login') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">Connexion</a>
            <a href="{{ route('register') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">S'inscrire</a>
        @endauth
    </div>
</header>

<script>
(function() {
    var btn   = document.getElementById('mobile-menu-btn');
    var menu  = document.getElementById('mobile-menu');
    var open  = document.getElementById('icon-open');
    var close = document.getElementById('icon-close');
    if (!btn) return;
    btn.addEventListener('click', function() {
        var hidden = menu.classList.toggle('hidden');
        open.classList.toggle('hidden', !hidden);
        close.classList.toggle('hidden', hidden);
    });
})();
</script>

{{-- Flash messages --}}
@if(session('success'))
<div data-flash class="max-w-7xl mx-auto px-6 mt-4">
    <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">✅ {{ session('success') }}</div>
</div>
@endif
@if(session('error'))
<div data-flash class="max-w-7xl mx-auto px-6 mt-4">
    <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">❌ {{ session('error') }}</div>
</div>
@endif

<main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    @yield('content')
</main>

<footer class="mt-16 border-t border-gray-200 bg-white">
    <div class="max-w-7xl mx-auto px-6 py-8 text-center text-sm text-gray-400">
        © {{ date('Y') }} La Tournée!. Tous droits réservés. — La consommation d'alcool est déconseillée aux moins de 18 ans.
    </div>
</footer>

</body>
</html>

