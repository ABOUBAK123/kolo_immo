@extends('layouts.app')

@section('title', 'Mon espace - Kolo Immo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- KYC Prompt -->
    @if(!$kycVerified)
    <div class="bg-gradient-to-r from-orange-400 to-yellow-500 rounded-2xl p-5 mb-6 text-white flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold">Vérifiez votre identité</p>
                <p class="text-sm text-white text-opacity-90">Débloquez toutes les fonctionnalités et augmentez votre score de confiance</p>
            </div>
        </div>
        <a href="{{ route('profile.kyc') }}" class="bg-white text-orange-600 font-bold px-5 py-2 rounded-xl text-sm hover:bg-orange-50 transition-colors flex-shrink-0">
            Vérifier maintenant →
        </a>
    </div>
    @endif

    <!-- Welcome -->
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bonjour, {{ Auth::user()->prenom ?? Auth::user()->name }} 👋</h1>
            <p class="text-gray-500">Gérez vos séjours et réservations</p>
        </div>
        <div class="flex items-center gap-3">
            @if($kycVerified)
            <div class="flex items-center gap-2 bg-green-100 text-green-700 text-sm font-semibold px-4 py-2 rounded-full">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Identité vérifiée
            </div>
            @endif
            <a href="{{ route('properties.index') }}" class="bg-blue-700 text-white font-semibold px-4 py-2 rounded-xl text-sm hover:bg-blue-800 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Chercher un logement
            </a>
        </div>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        @foreach([
            ['label' => 'Séjours effectués', 'value' => $totalTrips - $activeBookingsCount, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-green-100 text-green-700'],
            ['label' => 'Séjours à venir', 'value' => $activeBookingsCount, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'bg-blue-100 text-blue-700'],
            ['label' => 'Avis reçus', 'value' => $reviewsCount, 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => 'bg-yellow-100 text-yellow-600'],
        ] as $stat)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-14 h-14 {{ $stat['color'] }} rounded-2xl flex items-center justify-center flex-shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                <p class="text-gray-500 text-sm">{{ $stat['label'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upcoming bookings timeline -->
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Séjours à venir</h2>
                    <a href="{{ route('bookings.my-bookings') }}" class="text-blue-700 text-sm font-semibold hover:text-blue-900">Voir tout →</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($upcomingBookings ?? [] as $booking)
                    <div class="p-5 flex gap-4">
                        <div class="flex-shrink-0 text-center w-14">
                            <div class="bg-blue-700 text-white rounded-xl py-2 px-1">
                                <p class="text-xs font-semibold">{{ \Carbon\Carbon::parse($booking->check_in)->format('d') }}</p>
                                <p class="text-xs">{{ strtoupper(\Carbon\Carbon::parse($booking->check_in)->locale('fr')->isoFormat('MMM')) }}</p>
                            </div>
                            <div class="w-0.5 h-4 bg-gray-200 mx-auto mt-1"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $booking->property->title ?? 'Propriété' }}</h3>
                                    <p class="text-gray-500 text-xs mt-0.5">{{ $booking->property->city ?? '' }} · {{ \Carbon\Carbon::parse($booking->check_in)->diffInDays($booking->check_out) }} nuit(s)</p>
                                    <p class="text-xs text-blue-700 font-mono mt-1">Réf: #{{ $booking->reference }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    @php $days = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($booking->check_in), false); @endphp
                                    @if($days > 0)
                                    <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-2 py-1 rounded-full">Dans {{ $days }}j</span>
                                    @elseif($days === 0)
                                    <span class="bg-green-50 text-green-700 text-xs font-semibold px-2 py-1 rounded-full">Aujourd'hui</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-semibold text-blue-700 hover:text-blue-900">Voir les détails →</a>
                                @if($booking->conversation)
                                <span class="text-gray-300">·</span>
                                <a href="{{ route('messages.show', $booking->conversation) }}" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Messages</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-10 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 font-medium mb-2">Aucun séjour à venir</p>
                        <p class="text-gray-400 text-sm mb-4">Planifiez votre prochain voyage !</p>
                        <a href="{{ route('properties.index') }}" class="bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-blue-800 transition-colors">
                            Trouver un logement
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent activity -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Activité récente</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($recentActivity ?? [] as $booking)
                    @php $c = ['pending' => 'yellow', 'confirmed' => 'green', 'cancelled' => 'red', 'completed' => 'blue'][$booking->status] ?? 'gray'; @endphp
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0">
                            @if($booking->property->photos->first())
                            <img src="{{ asset('storage/' . $booking->property->photos->first()->path) }}" alt="" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-indigo-600"></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $booking->property->title ?? 'Propriété' }}</p>
                            <p class="text-xs text-gray-400">{{ $booking->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-{{ $c }}-100 text-{{ $c }}-800 flex-shrink-0">
                            {{ ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée'][$booking->status] ?? ucfirst($booking->status) }}
                        </span>
                    </div>
                    @empty
                    <div class="p-6 text-center text-gray-400 text-sm">Aucune activité récente</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-5">
            <!-- Profile card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center">
                <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center text-white font-bold text-2xl mb-3" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                    {{ strtoupper(substr(Auth::user()->prenom ?? Auth::user()->name, 0, 1)) }}
                </div>
                <h3 class="font-bold text-gray-900">{{ Auth::user()->prenom ?? Auth::user()->name }}</h3>
                <p class="text-gray-400 text-xs mt-0.5">{{ Auth::user()->email }}</p>
                @if(Auth::user()->city)
                <p class="text-gray-500 text-xs flex items-center justify-center gap-1 mt-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ Auth::user()->city }}
                </p>
                @endif
                <a href="{{ route('profile.kyc') }}" class="mt-4 inline-block text-sm font-semibold text-blue-700 hover:text-blue-900">
                    Modifier mon profil →
                </a>
            </div>

            <!-- Quick links -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-900 mb-4 text-sm">Accès rapide</h3>
                <div class="space-y-2">
                    @foreach([
                        ['href' => 'bookings.my-bookings', 'label' => 'Mes réservations', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['href' => 'messages.index', 'label' => 'Mes messages', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                        ['href' => 'profile.kyc', 'label' => 'Vérification KYC', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        ['href' => 'properties.index', 'label' => 'Chercher un logement', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
                    ] as $link)
                    <a href="{{ route($link['href']) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                        <svg class="w-4 h-4 text-gray-500 group-hover:text-blue-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                        </svg>
                        <span class="text-sm text-gray-700 group-hover:text-gray-900 font-medium">{{ $link['label'] }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
