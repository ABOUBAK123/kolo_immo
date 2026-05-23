<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') - Kolo Immo</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 16px; border-radius: 8px;
            color: #94A3B8; font-size: 14px; font-weight: 500;
            transition: all 0.2s; text-decoration: none;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: #212F3D; color: #F8FAFC;
        }
        .sidebar-link.active { color: #F39C12; }
        .sidebar-link svg { width: 18px; height: 18px; flex-shrink: 0; }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col"
        style="background-color: #1B2631;"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
        x-transition>

        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
            <div class="w-9 h-9 bg-accent-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-base">Kolo Immo</p>
                <p class="text-gray-400 text-xs">Administration</p>
            </div>
        </div>

        <!-- Nav Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Tableau de bord
            </a>

            <a href="{{ route('admin.users.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Utilisateurs
            </a>

            <a href="{{ route('admin.properties.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.properties.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Propriétés
            </a>

            <a href="{{ route('admin.bookings.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Réservations
            </a>

            <a href="{{ route('admin.kyc.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Vérification KYC
                @php $pendingKyc = \App\Models\KycDocument::where('status', 'pending')->count(); @endphp
                @if($pendingKyc > 0)
                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingKyc }}</span>
                @endif
            </a>

            <a href="{{ route('admin.disputes.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
                Litiges
            </a>

            <a href="{{ route('admin.reports.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Rapports
            </a>

            <a href="{{ route('admin.settings.show') }}"
                class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Paramètres
            </a>

            <div class="border-t border-gray-700 my-3"></div>

            <a href="{{ route('home') }}"
                class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Voir le site
            </a>
        </nav>

        <!-- User Info -->
        <div class="px-4 py-4 border-t border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-accent-500 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->prenom ?? Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ Auth::user()->prenom ?? Auth::user()->name ?? 'Admin' }}</p>
                    <p class="text-gray-400 text-xs">Administrateur</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-400 transition-colors" title="Déconnexion">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"></div>

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-64">
        <!-- Top bar -->
        <header class="bg-white shadow-sm sticky top-0 z-30 flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Tableau de bord')</h1>
            </div>

            <div class="flex items-center gap-4">
                <!-- Notifications -->
                <button class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- Admin info -->
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary-700 rounded-full flex items-center justify-center text-white text-sm font-bold">
                        {{ strtoupper(substr(Auth::user()->prenom ?? Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ Auth::user()->prenom ?? Auth::user()->name ?? 'Admin' }}</span>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center justify-between">
            <span class="text-sm font-medium">{{ session('success') }}</span>
            <button @click="show = false" class="text-green-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
            class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 flex items-center justify-between">
            <span class="text-sm font-medium">{{ session('error') }}</span>
            <button @click="show = false" class="text-red-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        <!-- Page content -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
