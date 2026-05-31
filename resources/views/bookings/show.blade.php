@extends('layouts.app')

@section('title', 'Réservation #' . $booking->reference . ' - Kolo Immo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Réservation <span class="text-blue-700">#{{ $booking->reference }}</span></h1>
            <p class="text-gray-500 text-sm mt-1">Créée le {{ $booking->created_at->format('d/m/Y à H:i') }}</p>
        </div>

        <!-- Status badge -->
        @php
        $statusConfig = [
            'pending'   => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-200', 'label' => 'En attente', 'dot' => 'bg-yellow-500'],
            'confirmed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200', 'label' => 'Confirmée', 'dot' => 'bg-green-500'],
            'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'label' => 'Annulée', 'dot' => 'bg-red-500'],
            'completed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200', 'label' => 'Terminée', 'dot' => 'bg-blue-500'],
            'rejected'  => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-200', 'label' => 'Refusée', 'dot' => 'bg-gray-500'],
        ];
        $sc = $statusConfig[$booking->status] ?? $statusConfig['pending'];
        @endphp
        <div class="flex items-center gap-2 {{ $sc['bg'] }} {{ $sc['text'] }} border {{ $sc['border'] }} px-4 py-2 rounded-full">
            <div class="w-2 h-2 rounded-full {{ $sc['dot'] }}"></div>
            <span class="font-semibold text-sm">{{ $sc['label'] }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left column -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Property card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex gap-4 p-5">
                    <div class="w-24 h-24 rounded-xl overflow-hidden flex-shrink-0">
                        @if($booking->property->photos->first())
                        <img src="{{ $booking->property->photos->first()->photoUrl() }}"
                            alt="{{ $booking->property->title }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full bg-gradient-to-br from-blue-400 to-indigo-600"></div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded capitalize">{{ $booking->property->type }}</span>
                        <h2 class="font-bold text-gray-900 mt-1">{{ $booking->property->title }}</h2>
                        <p class="text-gray-500 text-sm">{{ $booking->property->city }}</p>
                        <a href="{{ route('properties.show', $booking->property) }}" class="text-blue-700 text-xs font-semibold hover:text-blue-900 mt-1 inline-block">
                            Voir la propriété →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Booking details -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-900 mb-4">Détails du séjour</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs font-semibold text-gray-500 mb-1">ARRIVÉE</p>
                        <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400">Check-in: {{ $booking->property->check_in_time ?? '14:00' }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs font-semibold text-gray-500 mb-1">DÉPART</p>
                        <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400">Check-out: {{ $booking->property->check_out_time ?? '11:00' }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs font-semibold text-gray-500 mb-1">DURÉE</p>
                        <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($booking->check_in)->diffInDays($booking->check_out) }} nuit(s)</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs font-semibold text-gray-500 mb-1">VOYAGEURS</p>
                        <p class="font-bold text-gray-900">{{ $booking->guests }} personne(s)</p>
                    </div>
                </div>

                @if($booking->special_requests)
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-xl">
                    <p class="text-xs font-semibold text-yellow-700 mb-1">DEMANDES SPÉCIALES</p>
                    <p class="text-sm text-yellow-800">{{ $booking->special_requests }}</p>
                </div>
                @endif
            </div>

            <!-- Contract section -->
            @if($booking->contract)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Contrat de location</h3>
                    <span class="bg-{{ $booking->contract->status === 'fully_signed' ? 'green' : 'yellow' }}-100 text-{{ $booking->contract->status === 'fully_signed' ? 'green' : 'yellow' }}-800 text-xs font-semibold px-3 py-1 rounded-full">
                        {{ $booking->contract->status === 'fully_signed' ? 'Signé' : 'En attente de signature' }}
                    </span>
                </div>

                <!-- Signature status -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="p-3 rounded-xl border {{ $booking->contract->owner_signed_at ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center gap-2 mb-1">
                            @if($booking->contract->owner_signed_at)
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                            <svg class="w-4 h-4 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            @endif
                            <span class="text-xs font-semibold {{ $booking->contract->owner_signed_at ? 'text-green-800' : 'text-gray-600' }}">Propriétaire</span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $booking->contract->owner_signed_at ? $booking->contract->owner_signed_at->format('d/m/Y H:i') : 'En attente...' }}</p>
                    </div>
                    <div class="p-3 rounded-xl border {{ $booking->contract->tenant_signed_at ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center gap-2 mb-1">
                            @if($booking->contract->tenant_signed_at)
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                            <svg class="w-4 h-4 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            @endif
                            <span class="text-xs font-semibold {{ $booking->contract->tenant_signed_at ? 'text-green-800' : 'text-gray-600' }}">Locataire</span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $booking->contract->tenant_signed_at ? $booking->contract->tenant_signed_at->format('d/m/Y H:i') : 'En attente...' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('contracts.show', $booking->contract) }}"
                        class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Voir le contrat
                    </a>
                    @if($booking->contract->pdf_path)
                    <a href="{{ route('contracts.download', $booking->contract) }}"
                        class="flex items-center gap-2 border border-gray-200 text-gray-700 font-semibold px-4 py-2.5 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Télécharger PDF
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Payment section -->
            @if($booking->payments && $booking->payments->count() > 0)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-900 mb-4">Historique de paiement</h3>
                <div class="space-y-3">
                    @foreach($booking->payments as $payment)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-{{ $payment->status === 'completed' ? 'green' : ($payment->status === 'failed' ? 'red' : 'yellow') }}-100 flex items-center justify-center">
                                @if($payment->status === 'completed')
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                @elseif($payment->status === 'failed')
                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                @else
                                <svg class="w-4 h-4 text-yellow-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</p>
                                <p class="text-xs text-gray-400">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-900 text-sm">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</p>
                            <span class="text-xs font-semibold text-{{ $payment->status === 'completed' ? 'green' : ($payment->status === 'failed' ? 'red' : 'yellow') }}-600">
                                {{ ['completed' => 'Payé', 'failed' => 'Échoué', 'pending' => 'En cours'][$payment->status] ?? ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex flex-wrap gap-3">
                {{-- Pay with wallet --}}
                @if($booking->payment_status === 'pending' && Auth::id() === $booking->tenant_id)
                @php $wallet = Auth::user()->getOrCreateWallet(); @endphp
                <div class="w-full bg-blue-50 border border-blue-200 rounded-xl p-4 mb-1">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <p class="font-bold text-blue-900 text-sm">💳 Payer avec votre portefeuille</p>
                            <p class="text-xs text-blue-700 mt-0.5">
                                Solde : <strong>{{ $wallet->formattedBalance() }}</strong>
                                @if(!$wallet->hasSufficientBalance((float)$booking->total_amount))
                                · <span class="text-red-600 font-semibold">Solde insuffisant</span>
                                @endif
                            </p>
                        </div>
                        @if($wallet->hasSufficientBalance((float)$booking->total_amount))
                        <form action="{{ route('wallet.pay-booking', $booking) }}" method="POST"
                              onsubmit="return confirm('Confirmer le paiement de {{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA depuis votre portefeuille ?')">
                            @csrf
                            <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white font-bold px-5 py-2 rounded-lg text-sm transition-colors">
                                Payer {{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA →
                            </button>
                        </form>
                        @else
                        <a href="{{ route('wallet.index') }}"
                           class="bg-white border border-blue-300 text-blue-700 font-semibold px-4 py-2 rounded-lg text-sm hover:bg-blue-50 transition-colors">
                            + Recharger le portefeuille
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                @if($booking->status === 'pending' || $booking->status === 'confirmed')
                <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                    onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="flex items-center gap-2 border-2 border-red-200 text-red-700 font-semibold px-5 py-2.5 rounded-xl text-sm hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Annuler la réservation
                    </button>
                </form>
                @endif

                @if($booking->status === 'completed' && !$booking->review)
                <a href="{{ route('reviews.create', $booking) }}"
                    class="flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Laisser un avis
                </a>
                @endif

                @if(in_array($booking->status, ['confirmed', 'completed', 'refund_pending']) && $booking->status !== 'disputed')
                @php $existingDispute = $booking->disputes()->where('opened_by', Auth::id())->first() ?? null; @endphp
                @if(!$existingDispute)
                <a href="{{ route('disputes.create', $booking) }}"
                   class="flex items-center gap-2 border-2 border-orange-200 text-orange-700 font-semibold px-5 py-2.5 rounded-xl text-sm hover:bg-orange-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Ouvrir un litige
                </a>
                @else
                <a href="{{ route('disputes.show', $existingDispute) }}"
                   class="flex items-center gap-2 border-2 border-gray-200 text-gray-600 font-semibold px-5 py-2.5 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                    Voir mon litige
                </a>
                @endif
                @endif
            </div>
        </div>

        <!-- Right: Price summary + contact -->
        <div class="space-y-4">
            <!-- Price card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-900 mb-4">Récapitulatif financier</h3>
                @php
                $nights = \Carbon\Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
                $subtotal = $booking->property->price_per_night * $nights;
                $serviceFee = round($subtotal * 0.03);
                @endphp
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ number_format($booking->property->price_per_night, 0, ',', ' ') }} FCFA × {{ $nights }} nuit(s)</span>
                        <span>{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Frais de service (3%)</span>
                        <span>{{ number_format($serviceFee, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if($booking->property->deposit_amount)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Caution</span>
                        <span>{{ number_format($booking->property->deposit_amount, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-200 pt-2.5 flex justify-between font-bold text-base">
                        <span>TOTAL</span>
                        <span class="text-blue-700">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-900 mb-3">Communication</h3>
                @if($booking->conversation)
                <a href="{{ route('messages.show', $booking->conversation) }}"
                    class="flex items-center gap-3 w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold px-4 py-3 rounded-xl text-sm transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    Contacter le {{ Auth::id() === $booking->tenant_id ? 'propriétaire' : 'locataire' }}
                </a>
                @else
                <a href="{{ route('messages.create', ['booking' => $booking->id]) }}"
                    class="flex items-center gap-3 w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold px-4 py-3 rounded-xl text-sm transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    Envoyer un message
                </a>
                @endif
            </div>

            <!-- Help -->
            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-2 text-sm">Besoin d'aide ?</h3>
                <p class="text-xs text-gray-500 mb-3">Notre équipe est disponible 24h/24 pour vous aider.</p>
                <a href="#" class="text-blue-700 text-sm font-semibold hover:text-blue-900 flex items-center gap-1">
                    Contacter le support →
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
