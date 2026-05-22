@extends('layouts.app')

@section('title', 'Laisser un avis - Kolo Immo')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{
        overall: {{ old('overall_rating', 0) }},
        cleanliness: {{ old('cleanliness', 0) }},
        communication: {{ old('communication', 0) }},
        accuracy: {{ old('accuracy', 0) }},
        value: {{ old('value', 0) }},
        location: {{ old('location', 0) }},
        hoverRating: 0,
        setRating(category, value) { this[category] = value; },
        hoverStar(value) { this.hoverRating = value; },
        resetHover() { this.hoverRating = 0; },
        starColor(category, starIndex) {
            const effective = this.hoverRating || this[category];
            return starIndex <= effective ? 'text-yellow-400' : 'text-gray-200';
        }
    }">

    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Partagez votre expérience</h1>
        <p class="text-gray-500">Votre avis aide les autres voyageurs à faire le bon choix</p>
    </div>

    <!-- Booking summary -->
    @if(isset($booking))
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <div class="flex gap-4 items-center">
            <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0">
                @if($booking->property->photos->first())
                <img src="{{ asset('storage/' . $booking->property->photos->first()->path) }}" alt="{{ $booking->property->title }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full bg-gradient-to-br from-blue-400 to-indigo-600"></div>
                @endif
            </div>
            <div>
                <h3 class="font-bold text-gray-900">{{ $booking->property->title }}</h3>
                <p class="text-gray-500 text-sm">{{ $booking->property->city }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    Séjour: {{ \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') }}
                    · Réf: #{{ $booking->reference }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <form action="{{ route('reviews.store') }}" method="POST">
            @csrf
            @if(isset($booking))
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
            <input type="hidden" name="property_id" value="{{ $booking->property_id }}">
            @endif

            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            <!-- Overall rating -->
            <div class="mb-6 text-center">
                <h2 class="text-lg font-bold text-gray-900 mb-2">Note globale</h2>
                <p class="text-gray-500 text-sm mb-4">Comment évaluez-vous votre séjour dans l'ensemble ?</p>
                <div class="flex items-center justify-center gap-2">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button"
                        @click="setRating('overall', {{ $i }})"
                        @mouseover="hoverStar({{ $i }})"
                        @mouseleave="resetHover()"
                        class="p-1 transition-transform hover:scale-125">
                        <svg class="w-10 h-10 transition-colors" :class="starColor('overall', {{ $i }})" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </button>
                    @endfor
                </div>
                <p class="text-sm text-gray-500 mt-2" x-text="['', 'Très mauvais', 'Mauvais', 'Moyen', 'Bien', 'Excellent'][overall]"></p>
                <input type="hidden" name="overall_rating" :value="overall" required>
            </div>

            <!-- Sub-ratings -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                @foreach([
                    ['key' => 'cleanliness', 'name' => 'cleanliness', 'label' => 'Propreté', 'icon' => '✨'],
                    ['key' => 'communication', 'name' => 'communication', 'label' => 'Communication', 'icon' => '💬'],
                    ['key' => 'accuracy', 'name' => 'accuracy', 'label' => 'Conformité à l\'annonce', 'icon' => '📋'],
                    ['key' => 'value', 'name' => 'value', 'label' => 'Rapport qualité/prix', 'icon' => '💰'],
                    ['key' => 'location', 'name' => 'location', 'label' => 'Localisation', 'icon' => '📍'],
                ] as $criterion)
                <div class="p-4 bg-gray-50 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-700">{{ $criterion['icon'] }} {{ $criterion['label'] }}</span>
                        <span class="text-sm font-bold text-blue-700" x-text="{{ $criterion['key'] }} + '/5'"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" @click="setRating('{{ $criterion['key'] }}', {{ $i }})"
                            class="transition-transform hover:scale-110">
                            <svg class="w-6 h-6 transition-colors" :class="{{ $criterion['key'] }} >= {{ $i }} ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                        @endfor
                    </div>
                    <input type="hidden" name="{{ $criterion['name'] }}" :value="{{ $criterion['key'] }}">
                </div>
                @endforeach
            </div>

            <!-- Comment -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Votre commentaire</label>
                <textarea name="comment" rows="5" placeholder="Décrivez votre expérience: propreté, accueil, confort, quartier... Minimum 20 caractères."
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none {{ $errors->has('comment') ? 'border-red-300' : '' }}"
                    minlength="20" required>{{ old('comment') }}</textarea>
                @error('comment')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Submit -->
            <button type="submit" :disabled="overall === 0"
                class="w-full bg-yellow-400 hover:bg-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3.5 rounded-xl transition-all duration-200 text-sm shadow-sm hover:shadow-md">
                Publier mon avis
            </button>

            <div class="mt-4 p-3 bg-gray-50 rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-gray-500">Votre avis sera publié après validation par notre équipe. Les avis sont vérifiés et ne peuvent pas être modifiés après publication.</p>
            </div>
        </form>
    </div>
</div>
@endsection
