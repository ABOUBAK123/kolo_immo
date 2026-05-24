@extends('layouts.app')

@section('title', 'Mes réservations - Kolo Immo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Mes réservations</h1>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($bookings as $booking)
        @php
        $statusColor = ['pending' => 'yellow', 'confirmed' => 'green', 'cancelled' => 'red', 'completed' => 'blue', 'disputed' => 'purple'][$booking->status] ?? 'gray';
        $statusLabel = ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée', 'disputed' => 'Litige'][$booking->status] ?? ucfirst($booking->status);
        @endphp
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex">
                @if($booking->property && $booking->property->cover_photo)
                <img src="{{ $booking->property->coverPhotoUrl() }}" alt=""
                     class="w-28 h-28 object-cover flex-shrink-0">
                @else
                <div class="w-28 h-28 bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                @endif
                <div class="flex-1 p-4 flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono text-xs font-bold text-blue-600">{{ $booking->reference }}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">{{ $statusLabel }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900">{{ $booking->property->title ?? 'Logement supprimé' }}</h3>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ $booking->check_in->format('d/m/Y') }} → {{ $booking->check_out->format('d/m/Y') }}
                                · {{ $booking->nights }} nuit(s) · {{ $booking->guests }} voyageur(s)
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-gray-900">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-3">
                        <a href="{{ route('bookings.show', $booking) }}"
                           class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                            Détails
                        </a>
                        @if($booking->status === 'pending' && $booking->payment_status === 'pending')
                        <a href="{{ route('payments.initiate', $booking) }}"
                           class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700 transition">
                            Payer
                        </a>
                        @endif
                        @if($booking->status === 'completed' && !$booking->reviews->count())
                        <a href="{{ route('reviews.create', $booking) }}"
                           class="px-3 py-1.5 bg-yellow-50 text-yellow-700 rounded-lg text-xs font-semibold hover:bg-yellow-100 transition">
                            Laisser un avis
                        </a>
                        @endif
                        @if(in_array($booking->status, ['pending', 'confirmed']))
                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="inline" x-data="{ open: false }">
                            @csrf
                            <button type="button" @click="open = !open" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-semibold hover:bg-red-100 transition">
                                Annuler
                            </button>
                            <div x-show="open" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                                <div class="bg-white rounded-xl p-6 max-w-sm mx-4">
                                    <p class="font-semibold text-gray-900 mb-2">Annuler cette réservation ?</p>
                                    <p class="text-sm text-gray-500 mb-4">Cette action est irréversible. Des frais d'annulation peuvent s'appliquer.</p>
                                    <input type="text" name="reason" placeholder="Motif (optionnel)"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 focus:ring-2 focus:ring-red-400">
                                    <div class="flex gap-3">
                                        <button type="button" @click="open = false" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium">Retour</button>
                                        <button type="submit" class="flex-1 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold">Confirmer</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
            <p class="text-lg font-semibold text-gray-900">Aucune réservation</p>
            <p class="text-gray-400 text-sm mt-1">Vous n'avez pas encore effectué de réservation.</p>
            <a href="{{ route('properties.index') }}" class="mt-4 inline-block bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-800 transition-colors">
                Trouver un logement
            </a>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
</div>
@endsection
