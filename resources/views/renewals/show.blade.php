@extends('layouts.app')
@section('title', 'Renouvellement #' . $renewal->id)
@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="{{ route('bookings.show', $booking) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            ← Retour à la réservation
        </a>
    </div>

    {{-- Status header --}}
    @php $color = $renewal->statusColor(); @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-gray-900">Demande de renouvellement</h1>
            <span class="px-3 py-1.5 rounded-full text-sm font-bold bg-{{ $color }}-100 text-{{ $color }}-700">
                {{ $renewal->statusLabel() }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-400 mb-1">Date de fin actuelle</p>
                <p class="font-bold text-gray-900">{{ $renewal->current_end_date->format('d/m/Y') }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-3">
                <p class="text-xs text-gray-400 mb-1">Nouvelle date proposée</p>
                <p class="font-bold text-green-700">{{ $renewal->proposed_end_date->format('d/m/Y') }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-400 mb-1">Durée supplémentaire</p>
                <p class="font-bold text-gray-900">{{ $renewal->additional_nights }} nuits</p>
            </div>
            <div class="bg-blue-50 rounded-xl p-3">
                <p class="text-xs text-gray-400 mb-1">Montant/nuit proposé</p>
                <p class="font-bold text-blue-700">{{ number_format($renewal->proposed_amount, 0, ',', ' ') }} FCFA</p>
                @if($renewal->proposed_amount != $renewal->current_amount)
                <p class="text-xs {{ $renewal->proposed_amount > $renewal->current_amount ? 'text-red-500' : 'text-green-500' }} mt-0.5">
                    (actuellement {{ number_format($renewal->current_amount, 0, ',', ' ') }} FCFA)
                </p>
                @endif
            </div>
        </div>

        @if($renewal->note)
        <div class="bg-gray-50 rounded-xl p-3 mb-4">
            <p class="text-xs text-gray-400 mb-1">Message de {{ $renewal->initiatedBy->name }}</p>
            <p class="text-sm text-gray-700">{{ $renewal->note }}</p>
        </div>
        @endif

        <p class="text-xs text-gray-400">Demandé par <strong>{{ $renewal->initiatedBy->name }}</strong> · {{ $renewal->created_at->format('d/m/Y à H:i') }}</p>
        @if($renewal->respondedBy && $renewal->responded_at)
        <p class="text-xs text-gray-400 mt-1">Répondu par <strong>{{ $renewal->respondedBy->name }}</strong> · {{ $renewal->responded_at->format('d/m/Y à H:i') }}</p>
        @endif
    </div>

    {{-- Actions --}}
    @if($renewal->isPending() && Auth::id() !== $renewal->initiated_by)
    <div class="flex gap-3">
        <form action="{{ route('renewals.accept', $renewal) }}" method="POST" class="flex-1">
            @csrf @method('PATCH')
            <button type="submit"
                onclick="return confirm('Accepter ce renouvellement ? Le séjour sera prolongé.')"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl text-sm transition-colors">
                ✓ Accepter le renouvellement
            </button>
        </form>
        <form action="{{ route('renewals.reject', $renewal) }}" method="POST" class="flex-1">
            @csrf @method('PATCH')
            <button type="submit"
                onclick="return confirm('Refuser ce renouvellement ?')"
                class="w-full border-2 border-red-200 text-red-700 hover:bg-red-50 font-bold py-3 rounded-xl text-sm transition-colors">
                ✗ Refuser
            </button>
        </form>
    </div>
    @elseif($renewal->isAccepted())
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
        <p class="font-bold text-green-800">✓ Renouvellement accepté</p>
        <p class="text-sm text-green-600 mt-1">Le séjour est prolongé jusqu'au {{ $renewal->proposed_end_date->format('d/m/Y') }}.</p>
    </div>
    @endif

</div>
@endsection
