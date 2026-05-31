@extends('layouts.app')
@section('title', 'Demande de renouvellement')
@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="{{ route('bookings.show', $booking) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            ← Retour à la réservation
        </a>
    </div>

    {{-- Property header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6 flex items-center gap-4">
        @if($booking->property->cover_photo_url)
        <img src="{{ $booking->property->cover_photo_url }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
        @endif
        <div>
            <p class="font-bold text-gray-900">{{ $booking->property->title }}</p>
            <p class="text-sm text-gray-500">
                📅 Fin actuelle : <strong>{{ $booking->check_out->format('d/m/Y') }}</strong>
                ({{ $booking->check_out->diffForHumans() }})
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Locataire : {{ $booking->tenant->name }} · Propriétaire : {{ $booking->owner->name }}</p>
        </div>
    </div>

    <form action="{{ route('renewals.store', $booking) }}" method="POST"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <h2 class="font-bold text-gray-900 text-lg">Proposer un renouvellement</h2>

        {{-- Duration --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Durée supplémentaire *</label>
            <div class="flex gap-2 flex-wrap mb-3">
                @foreach([7 => '7 nuits', 14 => '14 nuits', 30 => '1 mois', 60 => '2 mois', 90 => '3 mois'] as $nights => $label)
                <button type="button"
                    onclick="document.getElementById('additional_nights').value = {{ $nights }}; updateEndDate()"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-sm font-semibold hover:bg-primary-50 hover:border-primary-300 transition-colors bg-white text-gray-700">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <input type="number" name="additional_nights" id="additional_nights"
                value="{{ old('additional_nights', 30) }}" min="1" max="365" required
                oninput="updateEndDate()"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            <p class="text-xs text-gray-500 mt-1">
                → Nouvelle date de fin : <strong id="new-end-date">—</strong>
            </p>
            @error('additional_nights')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Amount --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Montant par nuit proposé (FCFA) *</label>
            <input type="number" name="proposed_amount"
                value="{{ old('proposed_amount', $booking->price_per_night) }}" min="0" required
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            <p class="text-xs text-gray-400 mt-1">Montant actuel : {{ number_format($booking->price_per_night, 0, ',', ' ') }} FCFA/nuit</p>
            @error('proposed_amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Note --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Message (optionnel)</label>
            <textarea name="note" rows="3" maxlength="500"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                placeholder="Expliquez vos conditions de renouvellement...">{{ old('note') }}</textarea>
        </div>

        <button type="submit"
            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition-colors text-sm">
            🔄 Envoyer la demande de renouvellement
        </button>
    </form>
</div>

@push('scripts')
<script>
const checkOut = new Date('{{ $booking->check_out->format("Y-m-d") }}');
function updateEndDate() {
    const nights = parseInt(document.getElementById('additional_nights').value) || 0;
    const newEnd = new Date(checkOut);
    newEnd.setDate(newEnd.getDate() + nights);
    document.getElementById('new-end-date').textContent =
        newEnd.toLocaleDateString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric'});
}
updateEndDate();
</script>
@endpush
@endsection
