<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kolo Immo - Résidences meublées en Afrique de l\'Ouest')</title>
    <meta name="description" content="@yield('description', 'Trouvez et réservez des appartements, studios et villas meublés en Afrique de l\'Ouest. Paiements sécurisés via Mobile Money.')">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

<!-- Navigation -->
@php
    $allLanguages = \App\Http\Controllers\LocaleController::LANGUAGES;
    $currentLocale = app()->getLocale();
    $currentLang = $allLanguages[$currentLocale] ?? $allLanguages['fr'];
@endphp
<nav class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileOpen: false, userDropdown: false, langOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0">
                <div class="w-9 h-9 bg-primary-700 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-primary-700">Kolo <span class="text-accent-500">Immo</span></span>
            </a>

            <!-- Search Bar (Desktop) -->
            <form action="{{ route('properties.index') }}" method="GET" class="hidden md:flex items-center gap-2 bg-gray-100 rounded-full px-4 py-2 flex-1 max-w-lg mx-6">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="city" placeholder="Ville, quartier..." value="{{ request('city') }}"
                    class="bg-transparent flex-1 text-sm outline-none text-gray-700 placeholder-gray-400">
                <div class="h-4 w-px bg-gray-300"></div>
                <input type="date" name="check_in"
                    value="{{ preg_match('/^\d{4}-\d{2}-\d{2}$/', request('check_in', '')) ? request('check_in') : '' }}"
                    class="bg-transparent text-sm outline-none text-gray-500 w-28">
                <button type="submit" class="bg-accent-500 text-white rounded-full p-1.5 flex-shrink-0 hover:bg-accent-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>

            <!-- Desktop Nav Links -->
            <div class="hidden md:flex items-center gap-4">
                <a href="{{ route('owner.properties.create') }}" class="nav-link text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Publier un bien
                </a>

                <!-- Language Switcher -->
                <div class="relative" @click.away="langOpen = false">
                    <button @click="langOpen = !langOpen"
                        class="flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900 border border-gray-200 rounded-full px-3 py-1.5 hover:border-gray-300 transition-colors">
                        <span>{{ $currentLang['flag'] }}</span>
                        <span class="font-medium hidden lg:inline">{{ $currentLang['native'] }}</span>
                        <svg class="w-3 h-3 transition-transform" :class="langOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="langOpen" x-cloak x-transition
                        class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50">
                        @foreach($allLanguages as $code => $lang)
                        <form action="{{ route('language.change', $code) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-3 w-full px-4 py-2 text-sm hover:bg-gray-50 transition-colors {{ $currentLocale === $code ? 'font-semibold text-blue-700 bg-blue-50' : 'text-gray-700' }}">
                                <span class="text-base">{{ $lang['flag'] }}</span>
                                <span>{{ $lang['native'] }}</span>
                                @if($currentLocale === $code)
                                <svg class="w-3.5 h-3.5 ml-auto text-blue-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                @endif
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>

                @auth
                <a href="{{ route('messages.index') }}" class="nav-link text-sm relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </a>

                <!-- User Dropdown -->
                <div class="relative" @click.away="userDropdown = false">
                    <button @click="userDropdown = !userDropdown"
                        class="flex items-center gap-2 border border-gray-200 rounded-full pl-3 pr-1 py-1 hover:shadow-md transition-shadow">
                        <span class="text-sm font-medium text-gray-700">{{ Auth::user()->prenom ?? Auth::user()->name }}</span>
                        <div class="w-7 h-7 bg-primary-700 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            {{ mb_strtoupper(mb_substr(Auth::user()->prenom ?? Auth::user()->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                        </div>
                    </button>

                    <div x-show="userDropdown" x-cloak x-transition
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->prenom ?? Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Mon profil
                        </a>
                        @if(Auth::user()->role === 'owner')
                        <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Tableau de bord
                        </a>
                        @else
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Mon tableau de bord
                        </a>
                        @endif
                        <a href="{{ route('bookings.my-bookings') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Mes réservations
                        </a>
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="nav-link text-sm">Connexion</a>
                <a href="{{ route('register') }}" class="btn-primary text-sm">S'inscrire</a>
                @endauth
            </div>

            <!-- Mobile Hamburger -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileOpen" x-cloak x-transition class="md:hidden border-t border-gray-100 bg-white">
        <div class="px-4 py-3">
            <form action="{{ route('properties.index') }}" method="GET" class="flex items-center gap-2 bg-gray-100 rounded-full px-4 py-2.5 mb-4">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="city" placeholder="Rechercher une ville..." class="bg-transparent flex-1 text-sm outline-none text-gray-700">
                <button type="submit" class="bg-accent-500 text-white rounded-full p-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
            <!-- Mobile Language Switcher -->
            <div class="flex flex-wrap gap-2 mb-3 pb-3 border-b border-gray-100">
                <span class="text-xs text-gray-400 font-semibold uppercase tracking-wide w-full">{{ __('app.nav.language') }}</span>
                @foreach($allLanguages as $code => $lang)
                <form action="{{ route('language.change', $code) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm border transition-colors {{ $currentLocale === $code ? 'bg-blue-700 text-white border-blue-700 font-semibold' : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300' }}">
                        <span>{{ $lang['flag'] }}</span>
                        <span>{{ $lang['native'] }}</span>
                    </button>
                </form>
                @endforeach
            </div>

            <div class="space-y-1">
                <a href="{{ route('owner.properties.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Publier un bien
                </a>
                @auth
                <a href="{{ route('messages.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    Messages
                </a>
                <a href="{{ Auth::user()->role === 'owner' ? route('owner.dashboard') : route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Tableau de bord
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Déconnexion
                    </button>
                </form>
                @else
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                    Connexion
                </a>
                <a href="{{ route('register') }}" class="block mx-3 mt-2 btn-primary text-center text-sm">
                    S'inscrire
                </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
        <button @click="show = false" class="text-green-500 hover:text-green-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 6000)"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
        <button @click="show = false" class="text-red-500 hover:text-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('warning'))
<div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 6000)"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium">{{ session('warning') }}</span>
        </div>
        <button @click="show = false" class="text-yellow-500 hover:text-yellow-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif

<!-- Main Content -->
<main>
    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-primary-700 text-white mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="md:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-700" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold">Kolo <span class="text-accent-400">Immo</span></span>
                </div>
                <p class="text-primary-200 text-sm leading-relaxed">La plateforme de référence pour les résidences meublées en Afrique de l'Ouest.</p>
                <div class="flex items-center gap-3 mt-4">
                    <a href="#" class="w-8 h-8 bg-primary-600 hover:bg-primary-500 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="w-8 h-8 bg-primary-600 hover:bg-primary-500 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="w-8 h-8 bg-primary-600 hover:bg-primary-500 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg>
                    </a>
                </div>
            </div>

            <!-- Links 1 -->
            <div>
                <h3 class="font-semibold text-white mb-4">Plateforme</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('properties.index') }}" class="text-primary-200 hover:text-white text-sm transition-colors">Trouver un logement</a></li>
                    <li><a href="{{ route('owner.properties.create') }}" class="text-primary-200 hover:text-white text-sm transition-colors">Publier un bien</a></li>
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">Comment ça marche</a></li>
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">Tarifs et frais</a></li>
                </ul>
            </div>

            <!-- Links 2 -->
            <div>
                <h3 class="font-semibold text-white mb-4">Assistance</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">Centre d'aide</a></li>
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">Nous contacter</a></li>
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">Signaler un problème</a></li>
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">Litiges</a></li>
                </ul>
            </div>

            <!-- Links 3 -->
            <div>
                <h3 class="font-semibold text-white mb-4">Légal</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">À propos de nous</a></li>
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">Conditions d'utilisation</a></li>
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">Politique de confidentialité</a></li>
                    <li><a href="#" class="text-primary-200 hover:text-white text-sm transition-colors">Cookies</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-primary-600 mt-8 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-primary-200 text-sm">© 2026 Kolo Immo - Résidences meublées en Afrique de l'Ouest</p>
            <div class="flex items-center gap-4">
                <span class="text-primary-200 text-xs">Paiements sécurisés via</span>
                <div class="flex items-center gap-2">
                    <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded">Orange</span>
                    <span class="bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded">Wave</span>
                    <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">MTN</span>
                </div>
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
