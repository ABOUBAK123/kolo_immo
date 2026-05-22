@extends('layouts.app')

@section('title', 'Réservations en attente - Kolo Immo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('owner.bookings.index') }}" class="text-blue-600 hover:underline text-sm">← Toutes les réservations</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-700 font-medium">En attente ({{ $bookings->total() }})</span>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    @forelse($bookings as $booking)
    <div class="bg-white rounded-xl border border-yellow-200 shadow-sm p-5 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-mono text-sm font-bold text-blue-600">{{ $booking->reference }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">En attente</span>
                </div>

                <h3 class="font-bold text-gray-900">{{ $booking->property->title ?? '—' }}</h3>

                <div class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                    <div class="text-gray-500">Locataire :</div>
                    <div class="font-medium text-gray-900">{{ $booking->tenant->name ?? '—' }}</div>
                    <div class="text-gray-500">Téléphone :</div>
                    <div class="font-medium text-gray-900">{{ $booking->tenant->phone ?? '—' }}</div>
                    <div class="text-gray-500">Arrivée :</div>
                    <div class="font-medium text-gray-900">{{ $booking->check_in->format('d/m/Y') }}</div>
                    <div class="text-gray-500">Départ :</div>
                    <div class="font-medium text-gray-900">{{ $booking->check_out->format('d/m/Y') }}</div>
                    <div class="text-gray-500">Durée :</div>
                    <div class="font-medium text-gray-900">{{ $booking->nights }} nuit(s)</div>
                    <div class="text-gray-500">Voyageurs :</div>
                    <div class="font-medium text-gray-900">{{ $booking->guests }}</div>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="text-gray-500 text-sm">Total :</span>
                        <span class="text-lg font-bold text-gray-900 ml-2">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <span class="text-xs text-gray-400">Demande du {{ $booking->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div class="sm:w-48 flex flex-col gap-2">
                <form method="POST" action="{{ route('owner.bookings.confirm', $booking) }}">
                    @csrf
                    <button type="submit"
                        class="w-full py-2.5 bg-green-600 text-white rounded-xl font-semibold text-sm hover:bg-green-700 transition-colors text-center">
                        ✓ Accepter
                    </button>
                </form>

                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                        class="w-full py-2.5 bg-red-50 text-red-700 rounded-xl font-semibold text-sm hover:bg-red-100 transition-colors border border-red-200">
                        ✕ Refuser
                    </button>
                    <div x-show="open" x-cloak class="mt-2">
                        <form method="POST" action="{{ route('owner.bookings.reject', $booking) }}">
                            @csrf
                            <textarea name="reason" rows="2" required
                                placeholder="Motif du refus (obligatoire)..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:ring-1 focus:ring-red-400 resize-none mb-2"></textarea>
                            <button type="submit"
                                class="w-full py-2 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition-colors">
                                Confirmer le refus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="text-5xl mb-4">✅</div>
        <p class="text-lg font-semibold text-gray-900">Aucune réservation en attente</p>
        <p class="text-gray-500 text-sm mt-1">Toutes vos demandes ont été traitées.</p>
    </div>
    @endforelse

    <div class="mt-4">{{ $bookings->links() }}</div>
</div>
@endsection
