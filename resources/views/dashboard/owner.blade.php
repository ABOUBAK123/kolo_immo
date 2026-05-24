@extends('layouts.app')

@section('title', 'Tableau de bord propriétaire - Kolo Immo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-700 to-blue-600 rounded-2xl p-6 mb-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white opacity-5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold mb-1">Bonjour, {{ Auth::user()->prenom ?? Auth::user()->name }} 👋</h1>
                <p class="text-blue-100">Bienvenue sur votre tableau de bord propriétaire</p>
            </div>
            <div class="flex items-center gap-3">
                @if(Auth::user()->isKycVerified())
                <span class="flex items-center gap-2 bg-green-500 bg-opacity-20 border border-green-400 text-white text-sm font-semibold px-4 py-2 rounded-full">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    KYC Vérifié
                </span>
                @else
                <a href="{{ route('profile.kyc') }}" class="flex items-center gap-2 bg-yellow-500 bg-opacity-20 border border-yellow-400 text-white text-sm font-semibold px-4 py-2 rounded-full hover:bg-opacity-30 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Vérifier mon identité
                </a>
                @endif
                <a href="{{ route('owner.properties.create') }}" class="flex items-center gap-2 bg-white text-blue-700 font-bold px-4 py-2 rounded-xl text-sm hover:bg-blue-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Ajouter un bien
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['label' => 'Revenus ce mois', 'value' => number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ') . ' FCFA', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-green-600 bg-green-100', 'border' => 'border-green-200'],
            ['label' => 'Taux d\'occupation', 'value' => ($stats['occupancy_rate'] ?? 0) . '%', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color' => 'text-blue-600 bg-blue-100', 'border' => 'border-blue-200'],
            ['label' => 'Réservations en attente', 'value' => $stats['pending_bookings'] ?? 0, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'text-yellow-600 bg-yellow-100', 'border' => 'border-yellow-200'],
            ['label' => 'Note moyenne', 'value' => number_format($stats['average_rating'] ?? 0, 1) . ' ★', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => 'text-orange-500 bg-orange-100', 'border' => 'border-orange-200'],
        ] as $stat)
        <div class="bg-white rounded-2xl border {{ $stat['border'] }} p-5 shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 {{ $stat['color'] }} rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-900 mb-1">{{ $stat['value'] }}</p>
            <p class="text-gray-500 text-xs">{{ $stat['label'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main content -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Revenue chart placeholder -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-900">Revenus mensuels</h2>
                    <select class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none">
                        <option>2026</option>
                        <option>2025</option>
                    </select>
                </div>
                <div class="h-48 bg-gradient-to-t from-blue-50 to-white rounded-xl flex items-end gap-2 px-4 pt-4">
                    @foreach([60, 80, 45, 90, 70, 100, 75, 85, 60, 90, 110, 95] as $i => $height)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full rounded-t-md bg-blue-700" style="height: {{ $height * 0.4 + 10 }}px; opacity: {{ 0.5 + ($i * 0.04) }};"></div>
                        <span class="text-xs text-gray-400">{{ ['J','F','M','A','M','J','J','A','S','O','N','D'][$i] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent bookings table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Réservations récentes</h2>
                    <a href="{{ route('owner.bookings.index') }}" class="text-blue-700 text-sm font-semibold hover:text-blue-900">Voir tout →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                <th class="text-left px-4 py-3">Référence</th>
                                <th class="text-left px-4 py-3">Locataire</th>
                                <th class="text-left px-4 py-3 hidden md:table-cell">Bien</th>
                                <th class="text-left px-4 py-3 hidden lg:table-cell">Dates</th>
                                <th class="text-right px-4 py-3">Montant</th>
                                <th class="text-center px-4 py-3">Statut</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($recentBookings ?? [] as $booking)
                            @php
                            $colors = ['pending' => 'yellow', 'confirmed' => 'green', 'cancelled' => 'red', 'completed' => 'blue'];
                            $labels = ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée'];
                            $c = $colors[$booking->status] ?? 'gray';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-mono text-blue-700 font-bold">{{ $booking->reference }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $booking->tenant->prenom ?? $booking->tenant->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell max-w-32 truncate">{{ $booking->property->title ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500 hidden lg:table-cell">{{ \Carbon\Carbon::parse($booking->check_in)->format('d/m') }} – {{ \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($booking->total_amount, 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $c }}-100 text-{{ $c }}-800">
                                        {{ $labels[$booking->status] ?? ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('bookings.show', $booking) }}" class="text-blue-700 hover:text-blue-900 text-xs font-medium">Voir</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">Aucune réservation pour l'instant</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- My properties -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">Mes propriétés</h2>
                    <a href="{{ route('owner.properties.create') }}" class="text-blue-700 text-sm font-semibold hover:text-blue-900 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter
                    </a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($myProperties ?? [] as $property)
                    <div class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                        <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0">
                            @if($property->photos->first())
                            <img src="{{ $property->photos->first()->photoUrl() }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-indigo-600"></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 text-sm truncate">{{ $property->title }}</h3>
                            <p class="text-gray-400 text-xs">{{ $property->city }} · {{ $property->views_count ?? 0 }} vues</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $property->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $property->status === 'active' ? 'Actif' : 'Inactif' }}
                            </span>
                            <a href="{{ route('owner.properties.edit', $property) }}" class="text-blue-700 hover:text-blue-900 text-xs font-medium">Modifier</a>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center">
                        <p class="text-gray-400 text-sm mb-3">Vous n'avez pas encore de propriété publiée</p>
                        <a href="{{ route('owner.properties.create') }}" class="bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                            Publier mon premier bien
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar: Pending actions + Quick links -->
        <div class="space-y-5">
            <!-- Pending reservations -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 text-sm">À confirmer</h2>
                    @if(($pendingBookings ?? collect())->count() > 0)
                    <span class="w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                        {{ ($pendingBookings ?? collect())->count() }}
                    </span>
                    @endif
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($pendingBookings ?? [] as $booking)
                    <div class="p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-sm font-bold flex-shrink-0">
                                {{ substr($booking->tenant->prenom ?? $booking->tenant->name ?? 'L', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $booking->tenant->prenom ?? $booking->tenant->name }}</p>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($booking->check_in)->format('d/m') }} – {{ \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <form action="{{ route('owner.bookings.confirm', $booking) }}" method="POST" class="flex-1">
                                @csrf @method('PATCH')
                                <button class="w-full bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-2 rounded-lg transition-colors">
                                    Accepter
                                </button>
                            </form>
                            <form action="{{ route('owner.bookings.reject', $booking) }}" method="POST" class="flex-1">
                                @csrf @method('PATCH')
                                <button class="w-full bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold py-2 rounded-lg transition-colors border border-red-200">
                                    Refuser
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-gray-400 text-sm py-6">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Tout est à jour !
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick links -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="font-bold text-gray-900 mb-4 text-sm">Raccourcis</h2>
                <div class="space-y-2">
                    @foreach([
                        ['href' => 'owner.properties.create', 'label' => 'Publier un nouveau bien', 'icon' => 'M12 4v16m8-8H4', 'color' => 'text-blue-700'],
                        ['href' => 'messages.index', 'label' => 'Mes messages', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'color' => 'text-green-700'],
                        ['href' => 'profile.kyc', 'label' => 'Vérification KYC', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'text-purple-700'],
                        ['href' => 'profile.show', 'label' => 'Mon profil', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color' => 'text-gray-700'],
                    ] as $link)
                    <a href="{{ route($link['href']) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                        <div class="w-8 h-8 bg-gray-100 group-hover:bg-blue-100 rounded-lg flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4 {{ $link['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">{{ $link['label'] }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
