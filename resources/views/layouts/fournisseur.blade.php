<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Espace fournisseur') — La Tournée!</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-full flex flex-col">

    <header class="bg-white border-b border-gray-200 h-14 flex items-center px-4 md:px-6 gap-3 sticky top-0 z-40">
        <a href="{{ route('fournisseur.products.index') }}" class="flex items-center gap-2 min-w-0">
            <img src="/images/logo-la-tournee.svg" alt="La Tournée" class="h-8 w-8 flex-shrink-0">
            <span class="text-base font-bold text-gray-900 tracking-tight hidden sm:inline">
                La Tournée! <span class="font-normal text-gray-400 text-sm">— Fournisseur</span>
            </span>
        </a>

        <div class="ml-auto flex items-center gap-3">
            <a href="{{ route('fournisseur.profile.edit') }}"
               class="text-sm text-gray-600 hover:text-indigo-600 transition-colors flex items-center gap-1.5
                      {{ request()->routeIs('fournisseur.profile*') ? 'text-indigo-600 font-semibold' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm text-gray-400 hover:text-red-500 transition-colors whitespace-nowrap">
                    <span class="hidden sm:inline">Se déconnecter</span>
                    <svg class="w-5 h-5 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </header>

    @if(session('success'))
    <div class="mx-4 md:mx-6 mt-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mx-4 md:mx-6 mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
        ❌ {{ session('error') }}
    </div>
    @endif

    <main class="flex-1 px-4 md:px-6 py-6 max-w-5xl mx-auto w-full">
        @yield('content')
    </main>
</div>

</body>
</html>
