@extends('layouts.app')

@section('title', 'Réservation - ' . $property->title . ' - Kolo Immo')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    paymentMethod: '{{ old('payment_method', 'orange_money') }}',
    checkIn: '{{ request('check_in', old('check_in')) }}',
    checkOut: '{{ request('check_out', old('check_out')) }}',
    guests: {{ request('guests', old('guests', 1)) }},
    pricePerNight: {{ $property->price_per_night }},
    deposit: {{ $property->deposit_amount ?? 0 }},
    get nights() {
        if (!this.checkIn || !this.checkOut) return 0;
        const d1 = new Date(this.checkIn), d2 = new Date(this.checkOut);
        return Math.max(0, Math.round((d2 - d1) / 86400000));
    },
    get subtotal() { return this.nights * this.pricePerNight; },
    get serviceFee() { return Math.round(this.subtotal * 0.03); },
    get total() { return this.subtotal + this.serviceFee + this.deposit; }
}">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-blue-700">Accueil</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('properties.show', $property) }}" class="hover:text-blue-700 line-clamp-1">{{ $property->title }}</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-800 font-medium">Réservation</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        <!-- Left: Booking Form -->
        <div class="lg:col-span-3 space-y-6">
            <h1 class="text-2xl font-bold text-gray-900">Confirmer votre réservation</h1>

            <form action="{{ route('bookings.store') }}" method="POST" id="booking-form">
                @csrf
                <input type="hidden" name="property_id" value="{{ $property->id }}">

                <!-- Errors -->
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Dates -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <h2 class="font-bold text-gray-900 mb-4">Dates du séjour</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">ARRIVÉE</label>
                            <input type="date" name="check_in" x-model="checkIn"
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('check_in') ? 'border-red-300' : '' }}"
                                required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">DÉPART</label>
                            <input type="date" name="check_out" x-model="checkOut"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('check_out') ? 'border-red-300' : '' }}"
                                required>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-sm" x-show="nights > 0">
                        <span class="text-green-600 font-semibold" x-text="nights + ' nuit(s)'"></span>
                        <span class="text-gray-400">sélectionnée(s)</span>
                    </div>
                </div>

                <!-- Guests -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <h2 class="font-bold text-gray-900 mb-4">Nombre de voyageurs</h2>
                    <div class="flex items-center gap-4">
                        <button type="button" @click="if(guests > 1) guests--"
                            class="w-10 h-10 rounded-full border-2 border-gray-200 flex items-center justify-center text-gray-700 hover:border-blue-500 hover:text-blue-700 transition-colors font-bold text-xl">
                            −
                        </button>
                        <span class="text-xl font-bold text-gray-900 min-w-8 text-center" x-text="guests"></span>
                        <button type="button" @click="if(guests < {{ $property->capacity ?? 10 }}) guests++"
                            class="w-10 h-10 rounded-full border-2 border-gray-200 flex items-center justify-center text-gray-700 hover:border-blue-500 hover:text-blue-700 transition-colors font-bold text-xl">
                            +
                        </button>
                        <span class="text-sm text-gray-500">/ {{ $property->capacity ?? 10 }} max</span>
                    </div>
                    <input type="hidden" name="guests" :value="guests">
                </div>

                <!-- Special requests -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <h2 class="font-bold text-gray-900 mb-1">Demandes spéciales <span class="text-gray-400 font-normal text-sm">(facultatif)</span></h2>
                    <p class="text-xs text-gray-400 mb-3">Les demandes spéciales ne sont pas garanties</p>
                    <textarea name="special_requests" rows="3" placeholder="Arrivée tardive, lit bébé, allergies..."
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('special_requests') }}</textarea>
                </div>

                <!-- Payment method -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <h2 class="font-bold text-gray-900 mb-4">Mode de paiement</h2>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach([
                            ['value' => 'orange_money', 'label' => 'Orange Money', 'color' => 'bg-orange-500', 'text' => 'text-orange-700 border-orange-400 bg-orange-50'],
                            ['value' => 'wave', 'label' => 'Wave', 'color' => 'bg-blue-500', 'text' => 'text-blue-700 border-blue-400 bg-blue-50'],
                            ['value' => 'mtn_momo', 'label' => 'MTN Mobile Money', 'color' => 'bg-yellow-400', 'text' => 'text-yellow-700 border-yellow-400 bg-yellow-50'],
                            ['value' => 'moov_money', 'label' => 'Moov Money', 'color' => 'bg-red-500', 'text' => 'text-red-700 border-red-400 bg-red-50'],
                        ] as $method)
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="{{ $method['value'] }}"
                                x-model="paymentMethod" class="sr-only peer">
                            <div class="p-3 border-2 border-gray-200 rounded-xl transition-all
                                peer-checked:{{ $method['text'] }} hover:border-gray-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 {{ $method['color'] }} rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                        {{ strtoupper(substr($method['label'], 0, 2)) }}
                                    </div>
                                    <span class="font-semibold text-sm text-gray-800">{{ $method['label'] }}</span>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <!-- Payment phone -->
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Numéro de téléphone pour le paiement
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <input type="tel" name="payment_phone" value="{{ old('payment_phone', Auth::user()->phone ?? '') }}"
                                placeholder="+225 07 XX XX XX XX"
                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('payment_phone') ? 'border-red-300' : '' }}"
                                required>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Vous recevrez une demande de paiement sur ce numéro</p>
                    </div>
                </div>

                <!-- Escrow notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-700 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-900 mb-1">Paiement sécurisé par séquestre</p>
                            <p class="text-xs text-blue-700 leading-relaxed">Votre paiement est conservé en sécurité par Kolo Immo. Le propriétaire ne recevra les fonds qu'après votre arrivée confirmée. En cas d'annulation valide, vous êtes intégralement remboursé.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full bg-orange-400 hover:bg-orange-500 text-white font-bold py-4 rounded-2xl text-base transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Confirmer et payer
                    <span x-text="' — ' + total.toLocaleString('fr-FR') + ' FCFA'" x-show="nights > 0"></span>
                </button>

                <p class="text-center text-xs text-gray-400 mt-3">
                    En confirmant, vous acceptez les
                    <a href="#" class="text-blue-600 underline">Conditions de location</a>
                    de Kolo Immo
                </p>
            </form>
        </div>

        <!-- Right: Summary -->
        <div class="lg:col-span-2">
            <div class="sticky top-24 space-y-4">
                <!-- Property summary -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <div class="flex gap-4">
                        <div class="w-24 h-24 rounded-xl overflow-hidden flex-shrink-0">
                            @if($property->photos->first())
                            <img src="{{ asset('storage/' . $property->photos->first()->path) }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-indigo-600"></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded capitalize">{{ $property->type }}</span>
                            <h3 class="font-semibold text-gray-900 text-sm mt-1 line-clamp-2">{{ $property->title }}</h3>
                            <p class="text-gray-400 text-xs mt-1">{{ $property->city }}</p>
                            <div class="flex items-center gap-1 mt-1">
                                <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span class="text-xs font-semibold text-gray-700">{{ number_format($property->average_rating ?? 0, 1) }}</span>
                                <span class="text-gray-400 text-xs">({{ $property->reviews_count ?? 0 }} avis)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price breakdown -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">Détail du prix</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Prix par nuit</span>
                            <span class="font-medium">{{ number_format($property->price_per_night, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="nights > 0">
                            <span class="text-gray-600">× <span x-text="nights"></span> nuit(s)</span>
                            <span class="font-medium" x-text="subtotal.toLocaleString('fr-FR') + ' FCFA'"></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="nights > 0">
                            <span class="text-gray-600 flex items-center gap-1">
                                Frais de service
                                <span class="bg-gray-100 text-gray-500 text-xs px-1.5 py-0.5 rounded">3%</span>
                            </span>
                            <span class="font-medium" x-text="serviceFee.toLocaleString('fr-FR') + ' FCFA'"></span>
                        </div>
                        @if($property->deposit_amount)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 flex items-center gap-1">
                                Caution
                                <span class="bg-green-100 text-green-600 text-xs px-1.5 py-0.5 rounded">remboursable</span>
                            </span>
                            <span class="font-medium">{{ number_format($property->deposit_amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                        @endif
                        <div class="border-t border-gray-200 pt-3 flex justify-between font-bold text-base">
                            <span class="text-gray-900">TOTAL</span>
                            <span class="text-blue-700" x-show="nights > 0" x-text="total.toLocaleString('fr-FR') + ' FCFA'"></span>
                            <span class="text-gray-400" x-show="nights === 0">—</span>
                        </div>
                    </div>

                    <!-- Cancellation policy -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Politique d'annulation : <strong>{{ ucfirst($property->cancellation_policy ?? 'Flexible') }}</strong>.
                                @if($property->cancellation_policy === 'flexible')
                                Annulation gratuite jusqu'à 24h avant l'arrivée.
                                @elseif($property->cancellation_policy === 'moderate')
                                Remboursement 50% si annulation 5 jours avant.
                                @else
                                Pas de remboursement après confirmation.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
