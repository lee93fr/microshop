<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — La Tournée!</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Sidebar expanded / collapsed */
        #sidebar         { width: 16rem; }
        #main-content    { padding-left: 16rem; }

        body.sidebar-collapsed #sidebar      { width: 4rem; }
        body.sidebar-collapsed #main-content { padding-left: 4rem; }

        #sidebar, #main-content { transition: width 0.2s ease, padding-left 0.2s ease; }

        /* Labels & textes masqués en mode collapsed */
        body.sidebar-collapsed .sidebar-label   { display: none; }
        body.sidebar-collapsed .sidebar-user    { display: none; }
        body.sidebar-collapsed .sidebar-logo-text { display: none; }
        body.sidebar-collapsed .sidebar-divider  { display: none; }

        /* Centrer les icônes en collapsed */
        body.sidebar-collapsed .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; }
        body.sidebar-collapsed .sidebar-icon { margin: 0; }

        /* Sur mobile : sidebar est un drawer par-dessus le contenu */
        @media (max-width: 767px) {
            #sidebar      { width: 16rem !important; transform: translateX(-100%); transition: transform 0.2s ease; }
            #main-content { padding-left: 0 !important; }
            body.sidebar-open #sidebar { transform: translateX(0); }
        }

        /* Masquer prix d'achat / marges */
        body.hide-costs .col-purchase,
        body.hide-costs .col-margin { display: none !important; }
    </style>
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-full flex">

    {{-- Overlay mobile --}}
    <div id="sidebar-overlay"
         class="fixed inset-0 z-40 bg-black/50 hidden"
         onclick="closeMobileSidebar()"></div>

    {{-- Sidebar --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 bg-gray-900 flex flex-col overflow-hidden">

        {{-- Logo --}}
        <div class="flex h-16 items-center px-4 border-b border-gray-700/50 flex-shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 min-w-0">
                <img src="/images/logo-la-tournee.svg" alt="La Tournée" class="h-8 w-8 flex-shrink-0 brightness-0 invert">
                <span class="sidebar-logo-text text-sm font-bold text-white tracking-tight whitespace-nowrap">La Tournée!</span>
            </a>
            {{-- Fermer drawer mobile --}}
            <button onclick="closeMobileSidebar()" class="ml-auto text-gray-400 hover:text-white md:hidden flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto overflow-x-hidden">
            @php
                $nav = [
                    ['route' => 'admin.dashboard',            'label' => 'Tableau de bord', 'icon' => '📊', 'match' => 'admin.dashboard'],
                    ['route' => 'admin.commandes.index',      'label' => 'Commandes',        'icon' => '📋', 'match' => 'admin.commandes*'],
                    ['route' => 'admin.produits.index',       'label' => 'Produits',         'icon' => '🍷', 'match' => 'admin.produits*'],
                    ['route' => 'admin.categories.index',     'label' => 'Catégories',       'icon' => '🗂️', 'match' => 'admin.categories*'],
                    ['route' => 'admin.promo-codes.index',    'label' => 'Codes promo',      'icon' => '🎁', 'match' => 'admin.promo-codes*'],
                    ['route' => 'admin.supplier-orders.index','label' => 'Bons fournisseur', 'icon' => '📦', 'match' => 'admin.supplier-orders*'],
                    ['route' => 'admin.users.index',          'label' => 'Utilisateurs',     'icon' => '👥', 'match' => 'admin.users*'],
                    ['route' => 'admin.notifications.index',  'label' => 'Notifications',    'icon' => '📧', 'match' => 'admin.notifications*'],
                ];
            @endphp

            @foreach($nav as $item)
            <a href="{{ route($item['route']) }}"
               title="{{ $item['label'] }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                      {{ request()->routeIs($item['match'])
                         ? 'bg-indigo-600 text-white'
                         : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <span class="sidebar-icon text-base flex-shrink-0">{{ $item['icon'] }}</span>
                <span class="sidebar-label">{{ $item['label'] }}</span>
            </a>
            @endforeach

            @if(auth()->user()->isSuperAdmin())
            <div class="sidebar-divider pt-3 mt-3 border-t border-gray-700/50">
                <a href="{{ route('admin.settings.edit') }}"
                   title="Paramètres"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors whitespace-nowrap
                          {{ request()->routeIs('admin.settings*') ? 'bg-indigo-600 text-white' : '' }}">
                    <span class="sidebar-icon text-base flex-shrink-0">⚙️</span>
                    <span class="sidebar-label">Paramètres</span>
                </a>
            </div>
            @endif
        </nav>

        {{-- Footer sidebar --}}
        <div class="border-t border-gray-700/50 p-3 flex-shrink-0">
            {{-- Infos utilisateur --}}
            <div class="sidebar-user flex items-center gap-2 mb-3 min-w-0">
                <div class="h-7 w-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-medium text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-400 capitalize truncate">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                </div>
            </div>

            {{-- Voir boutique --}}
            <a href="{{ route('catalog.index') }}" target="_blank" title="Voir la boutique"
               class="sidebar-link flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs text-gray-400 hover:text-white hover:bg-gray-800 transition-colors mb-1 whitespace-nowrap">
                <span class="sidebar-icon flex-shrink-0">🛍️</span>
                <span class="sidebar-label">Voir la boutique</span>
            </a>

            {{-- Déconnexion --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Se déconnecter"
                        class="sidebar-link w-full flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs text-gray-400 hover:text-red-400 hover:bg-gray-800 transition-colors whitespace-nowrap">
                    <span class="sidebar-icon flex-shrink-0">↩</span>
                    <span class="sidebar-label">Se déconnecter</span>
                </button>
            </form>

            {{-- Bouton masquer prix d'achat --}}
            <button onclick="toggleCosts()" title="Masquer/afficher les prix d'achat et marges"
                    class="sidebar-link w-full flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs text-gray-500 hover:text-white hover:bg-gray-800 transition-colors whitespace-nowrap">
                <span id="costs-icon" class="sidebar-icon flex-shrink-0">👁</span>
                <span class="sidebar-label" id="costs-label">Masquer</span>
            </button>

            {{-- Bouton collapse (desktop uniquement) --}}
            <button onclick="toggleCollapse()" title="Réduire le menu"
                    class="hidden md:flex sidebar-link w-full items-center gap-2 px-2 py-1.5 mt-2 rounded-lg text-xs text-gray-500 hover:text-white hover:bg-gray-800 transition-colors whitespace-nowrap border-t border-gray-700/50 pt-2">
                <span id="collapse-icon" class="sidebar-icon flex-shrink-0 text-base">◀</span>
                <span class="sidebar-label">Réduire</span>
            </button>
        </div>
    </aside>

    {{-- Contenu principal --}}
    <div id="main-content" class="flex flex-col flex-1 min-h-full min-w-0">

        {{-- Header --}}
        <header class="sticky top-0 z-40 bg-white border-b border-gray-200 flex flex-wrap items-center px-4 md:px-6 gap-x-3 gap-y-2 py-3 md:h-16 md:py-0">
            {{-- Hamburger mobile --}}
            <button onclick="openMobileSidebar()" class="md:hidden text-gray-500 hover:text-gray-900 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-base md:text-lg font-semibold text-gray-900 truncate">@yield('header', 'Dashboard')</h1>
            <div class="ml-auto flex items-center gap-2 flex-wrap">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div data-flash class="mx-4 md:mx-6 mt-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm flex items-center gap-2">
            ✅ {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div data-flash class="mx-4 md:mx-6 mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm flex items-center gap-2">
            ❌ {{ session('error') }}
        </div>
        @endif

        <main class="flex-1 px-4 md:px-6 py-6">
            @yield('content')
        </main>
    </div>
</div>

<script>
// ── Collapse desktop ──────────────────────────────────────────
var COLLAPSED_KEY = 'admin_sidebar_collapsed';

function applyCollapse(animate) {
    var collapsed = localStorage.getItem(COLLAPSED_KEY) === '1';
    if (!animate) {
        document.getElementById('sidebar').style.transition = 'none';
        document.getElementById('main-content').style.transition = 'none';
    }
    if (collapsed) {
        document.body.classList.add('sidebar-collapsed');
        document.getElementById('collapse-icon').textContent = '▶';
    } else {
        document.body.classList.remove('sidebar-collapsed');
        document.getElementById('collapse-icon').textContent = '◀';
    }
    if (!animate) {
        requestAnimationFrame(function() {
            document.getElementById('sidebar').style.transition = '';
            document.getElementById('main-content').style.transition = '';
        });
    }
}

function toggleCollapse() {
    var collapsed = localStorage.getItem(COLLAPSED_KEY) === '1';
    localStorage.setItem(COLLAPSED_KEY, collapsed ? '0' : '1');
    applyCollapse(true);
}

// Appliquer sans animation au chargement
applyCollapse(false);

// ── Masquer prix d'achat / marges (global) ───────────────────
var COSTS_KEY = 'admin_hide_costs';

function applyCosts() {
    var hidden = localStorage.getItem(COSTS_KEY) === '1';
    if (hidden) {
        document.body.classList.add('hide-costs');
    } else {
        document.body.classList.remove('hide-costs');
    }
    var icon  = document.getElementById('costs-icon');
    var label = document.getElementById('costs-label');
    if (icon)  icon.textContent  = hidden ? '🚫' : '👁';
    if (label) label.textContent = hidden ? 'Afficher' : 'Masquer';
}

function toggleCosts() {
    var hidden = localStorage.getItem(COSTS_KEY) === '1';
    localStorage.setItem(COSTS_KEY, hidden ? '0' : '1');
    applyCosts();
}

applyCosts();

// ── Drawer mobile ─────────────────────────────────────────────
function openMobileSidebar() {
    document.body.classList.add('sidebar-open');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeMobileSidebar() {
    document.body.classList.remove('sidebar-open');
    document.getElementById('sidebar-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}

window.addEventListener('resize', function() {
    if (window.innerWidth >= 768) closeMobileSidebar();
});
</script>

</body>
</html>
