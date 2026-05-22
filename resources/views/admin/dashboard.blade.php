@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('content')

<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @foreach([
        ['label' => 'Utilisateurs', 'value' => $stats['users_count'] ?? 0, 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'bg-blue-500', 'change' => '+12%'],
        ['label' => 'Propriétés actives', 'value' => $stats['active_properties'] ?? 0, 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'color' => 'bg-green-500', 'change' => '+8%'],
        ['label' => 'Réservations total', 'value' => $stats['bookings_count'] ?? 0, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'bg-purple-500', 'change' => '+25%'],
        ['label' => 'Revenus plateforme', 'value' => number_format($stats['platform_revenue'] ?? 0, 0, ',', ' ') . ' FCFA', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-yellow-500', 'change' => '+18%'],
        ['label' => 'KYC en attente', 'value' => $stats['pending_kyc'] ?? 0, 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'bg-orange-500', 'change' => ''],
        ['label' => 'Litiges ouverts', 'value' => $stats['open_disputes'] ?? 0, 'icon' => 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9', 'color' => 'bg-red-500', 'change' => ''],
    ] as $stat)
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 {{ $stat['color'] }} rounded-lg flex items-center justify-center">
                <svg class="w-4.5 h-4.5 text-white w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                </svg>
            </div>
            @if($stat['change'])
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-1.5 py-0.5 rounded">{{ $stat['change'] }}</span>
            @endif
        </div>
        <p class="text-xl font-bold text-gray-900 mb-0.5">{{ $stat['value'] }}</p>
        <p class="text-gray-500 text-xs">{{ $stat['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Recent bookings -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-900">Réservations récentes</h2>
            <a href="{{ route('admin.bookings.index') }}" class="text-blue-600 text-xs font-semibold hover:text-blue-800">Voir tout →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase">
                        <th class="text-left px-5 py-3">Réf.</th>
                        <th class="text-left px-5 py-3">Locataire</th>
                        <th class="text-right px-5 py-3">Montant</th>
                        <th class="text-center px-5 py-3">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentBookings ?? [] as $booking)
                    @php
                    $c = ['pending' => 'yellow', 'confirmed' => 'green', 'cancelled' => 'red', 'completed' => 'blue'][$booking->status] ?? 'gray';
                    $l = ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée'][$booking->status] ?? ucfirst($booking->status);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono text-blue-600 text-xs font-bold">{{ $booking->reference }}</td>
                        <td class="px-5 py-3 text-gray-800">{{ $booking->tenant->prenom ?? $booking->tenant->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ number_format($booking->total_amount, 0, ',', ' ') }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $c }}-100 text-{{ $c }}-800">{{ $l }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400">Aucune réservation</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent users -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-900">Nouveaux utilisateurs</h2>
            <a href="{{ route('admin.users.index') }}" class="text-blue-600 text-xs font-semibold hover:text-blue-800">Voir tout →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentUsers ?? [] as $user)
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                    {{ strtoupper(substr($user->prenom ?? $user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $user->prenom ?? $user->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $user->email ?? $user->phone }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $user->role === 'owner' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $user->role === 'owner' ? 'Propriétaire' : 'Locataire' }}
                    </span>
                    @if($user->kyc_verified)
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-gray-400 text-sm">Aucun utilisateur récent</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Bookings by status -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
    <h2 class="font-bold text-gray-900 mb-4">Réservations par statut</h2>
    <div class="flex flex-wrap gap-4">
        @foreach([
            ['label' => 'En attente', 'count' => $bookingsByStatus['pending'] ?? 0, 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
            ['label' => 'Confirmées', 'count' => $bookingsByStatus['confirmed'] ?? 0, 'color' => 'bg-green-100 text-green-800 border-green-200'],
            ['label' => 'Annulées', 'count' => $bookingsByStatus['cancelled'] ?? 0, 'color' => 'bg-red-100 text-red-800 border-red-200'],
            ['label' => 'Terminées', 'count' => $bookingsByStatus['completed'] ?? 0, 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
        ] as $item)
        <div class="flex items-center gap-3 px-5 py-3 {{ $item['color'] }} border rounded-xl">
            <span class="text-2xl font-bold">{{ $item['count'] }}</span>
            <span class="font-medium">{{ $item['label'] }}</span>
        </div>
        @endforeach
    </div>
</div>

@endsection
