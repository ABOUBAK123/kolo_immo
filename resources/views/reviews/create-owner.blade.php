@extends('layouts.app')
@section('title', 'Évaluer le locataire')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Évaluer votre locataire</h1>
        <p class="text-gray-500 mt-1">Votre retour aide la communauté Kolo Immo</p>
    </div>

    {{-- Tenant card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6 flex items-center gap-4">
        <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
             style="background: linear-gradient(135deg,#1B4F72,#3498DB)">
            {{ strtoupper(substr($booking->tenant->name, 0, 1)) }}
        </div>
        <div>
            <p class="font-bold text-gray-900 text-lg">{{ $booking->tenant->name }}</p>
            <p class="text-sm text-gray-500">
                Séjour du {{ $booking->check_in->format('d/m/Y') }} au {{ $booking->check_out->format('d/m/Y') }}
                — {{ $booking->property->title }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Réf. {{ $booking->reference }}</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('owner.reviews.store', $booking) }}" method="POST"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6"
          x-data="ownerReview()">
        @csrf

        {{-- Overall --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Note générale *</label>
            <div class="flex gap-2">
                @for($i = 1; $i <= 5; $i++)
                <button type="button"
                    @click="setRating('overall', {{ $i }})"
                    @mouseover="hover.overall = {{ $i }}"
                    @mouseleave="hover.overall = 0"
                    class="text-4xl transition-transform hover:scale-110 focus:outline-none">
                    <span :class="(hover.overall || ratings.overall) >= {{ $i }} ? 'text-amber-400' : 'text-gray-200'">★</span>
                </button>
                @endfor
            </div>
            <input type="hidden" name="rating_overall" :value="ratings.overall">
            <p class="text-xs text-gray-500 mt-1" x-text="labels[ratings.overall] ?? 'Cliquez pour noter'"></p>
            @error('rating_overall')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Sub-ratings --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @php
                $criteria = [
                    'cleanliness'   => ['label' => 'Soin du logement', 'icon' => '🧹'],
                    'communication' => ['label' => 'Communication',    'icon' => '💬'],
                    'payment'       => ['label' => 'Paiement',         'icon' => '💳'],
                ];
            @endphp
            @foreach($criteria as $key => $c)
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">{{ $c['icon'] }} {{ $c['label'] }}</p>
                <div class="flex gap-1">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button"
                        @click="setRating('{{ $key }}', {{ $i }})"
                        @mouseover="hover.{{ $key }} = {{ $i }}"
                        @mouseleave="hover.{{ $key }} = 0"
                        class="text-2xl transition-transform hover:scale-110 focus:outline-none">
                        <span :class="(hover.{{ $key }} || ratings.{{ $key }}) >= {{ $i }} ? 'text-amber-400' : 'text-gray-200'">★</span>
                    </button>
                    @endfor
                </div>
                <input type="hidden" name="rating_{{ $key }}" :value="ratings.{{ $key }}">
                @error("rating_{$key}")<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endforeach
        </div>

        {{-- Comment --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Commentaire *</label>
            <textarea name="comment" rows="4" maxlength="1000" x-model="comment"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                placeholder="Décrivez votre expérience avec ce locataire : respect des lieux, communication, ponctualité...">{{ old('comment') }}</textarea>
            <div class="flex justify-between mt-1">
                <span class="text-xs text-gray-400" x-text="comment.length + '/1000'"></span>
                @error('comment')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
            :disabled="ratings.overall === 0 || ratings.cleanliness === 0 || ratings.communication === 0 || ratings.payment === 0 || comment.length < 20"
            class="w-full bg-amber-500 hover:bg-amber-600 disabled:bg-gray-200 disabled:cursor-not-allowed text-white font-bold py-3 rounded-xl transition-colors text-sm">
            Publier mon évaluation
        </button>
    </form>
</div>

@push('scripts')
<script>
function ownerReview() {
    return {
        ratings: { overall: 0, cleanliness: 0, communication: 0, payment: 0 },
        hover:   { overall: 0, cleanliness: 0, communication: 0, payment: 0 },
        comment: '',
        labels:  { 1: 'Mauvais', 2: 'Passable', 3: 'Bien', 4: 'Très bien', 5: 'Excellent' },
        setRating(key, val) { this.ratings[key] = val; },
    };
}
</script>
@endpush
@endsection
