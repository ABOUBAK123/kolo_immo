@extends('layouts.app')

@section('title', 'Mes réservations - Kolo Immo')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Réservations reçues</h1>
            <p class="text-gray-500 text-sm mt-0.5">Toutes les réservations pour vos logements</p>
        </div>
        @if($bookings->where('status', 'pending')->count() > 0)
        <a href="{{ route('owner.bookings.pending') }}"
           class="flex items-center gap-2 bg-yellow-500 text-white font-semibold px-4 py-2 rounded-xl text-sm hover:bg-yellow-600 transition-colors">
            En attente
            <span class="bg-white text-yellow-700 font-bold text-xs rounded-full px-2 py-0.5">{{ $bookings->where('status', 'pending')->count() }}</span>
        </a>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($bookings as $booking)
        @php
        $statusColor = ['pending' => 'yellow', 'confirmed' => 'green', 'cancelled' => 'red', 'completed' => 'blue', 'disputed' => 'purple'][$booking->status] ?? 'gray';
        $statusLabel = ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée', 'disputed' => 'Litige'][$booking->status] ?? ucfirst($booking->status);
        $payLabel = ['pending' => 'Non payé', 'paid' => 'Payé', 'escrowed' => 'Séquestre', 'released' => 'Libéré', 'refunded' => 'Remboursé'][$booking->payment_status] ?? $booking->payment_status;
        @endphp
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-mono text-sm font-bold text-blue-600">{{ $booking->reference }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">{{ $statusLabel }}</span>
                    </div>
                    <p class="font-semibold text-gray-900">{{ $booking->property->title ?? 'Logement supprimé' }}</p>
                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
                        <span>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $booking->tenant->name ?? '—' }}
                        </span>
                        <span>{{ $booking->guests }} voyageur(s)</span>
                        <span>{{ $booking->nights }} nuit(s)</span>
                    </div>
                    <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                        <span>{{ $booking->check_in->format('d/m/Y') }} → {{ $booking->check_out->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xl font-bold text-gray-900">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</p>
                    <p class="text-xs text-gray-400">{{ $payLabel }}</p>
                    @if($booking->status === 'pending')
                    <div class="flex gap-2 mt-3 justify-end">
                        <form method="POST" action="{{ route('owner.bookings.confirm', $booking) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700 transition">
                                Confirmer
                            </button>
                        </form>
                        <button type="button" onclick="document.getElementById('reject-{{ $booking->id }}').classList.toggle('hidden')"
                            class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-semibold hover:bg-red-100 transition">
                            Refuser
                        </button>
                    </div>
                    <div id="reject-{{ $booking->id }}" class="hidden mt-2">
                        <form method="POST" action="{{ route('owner.bookings.reject', $booking) }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="reason" placeholder="Motif du refus..."
                                class="border border-gray-300 rounded-lg px-2 py-1 text-xs w-40 focus:ring-1 focus:ring-red-500" required>
                            <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded-lg text-xs font-semibold">Envoyer</button>
                        </form>
                    </div>
                    @endif

                    {{-- Completed: rate tenant + dispute --}}
                    @if($booking->status === 'completed')
                    @php
                        $alreadyRated = $booking->reviews()->where('reviewer_id', Auth::id())->where('type','owner_to_tenant')->exists();
                    @endphp
                    <div class="flex gap-2 mt-3 justify-end">
                        @if(!$alreadyRated)
                        <a href="{{ route('owner.reviews.create', $booking) }}"
                           class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-xs font-semibold hover:bg-amber-600 transition">
                            ⭐ Évaluer le locataire
                        </a>
                        @else
                        <span class="px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-semibold">✓ Évaluation envoyée</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
            <p class="text-lg font-semibold text-gray-900">Aucune réservation</p>
            <p class="text-gray-400 text-sm mt-1">Vous n'avez pas encore reçu de réservations.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
</div>
@endsection
