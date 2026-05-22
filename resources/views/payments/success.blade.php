@extends('layouts.app')

@section('title', 'Paiement confirmé - Kolo Immo')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">

        <!-- Success animation -->
        <div class="relative inline-block mb-8">
            <div class="w-32 h-32 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                <div class="w-24 h-24 bg-green-200 rounded-full flex items-center justify-center">
                    <svg class="w-14 h-14 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Paiement confirmé !</h1>
        <p class="text-gray-500 text-lg mb-6">Votre réservation est sécurisée</p>

        @if(isset($booking))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-left mb-6">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="text-green-700 font-semibold text-sm">Réservation confirmée</span>
            </div>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Référence</span>
                    <span class="font-mono font-bold text-blue-700">#{{ $booking->reference }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Propriété</span>
                    <span class="font-medium text-gray-900 text-right max-w-48">{{ $booking->property->title }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Ville</span>
                    <span class="font-medium text-gray-900">{{ $booking->property->city }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Arrivée</span>
                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Départ</span>
                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between font-bold border-t border-gray-100 pt-2.5">
                    <span>Montant payé</span>
                    <span class="text-green-700">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Info boxes -->
        <div class="space-y-3 mb-8 text-left">
            <div class="flex items-start gap-3 p-4 bg-blue-50 rounded-xl border border-blue-200">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-blue-800">Un email de confirmation a été envoyé à {{ Auth::user()->email }}</p>
            </div>
            <div class="flex items-start gap-3 p-4 bg-green-50 rounded-xl border border-green-200">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <p class="text-sm text-green-800">Vos fonds sont sécurisés en séquestre jusqu'à votre arrivée</p>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="flex flex-col gap-3">
            @if(isset($booking))
            <a href="{{ route('bookings.show', $booking) }}"
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-lg flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Voir ma réservation
            </a>
            @if($booking->contract)
            <a href="{{ route('contracts.show', $booking->contract) }}"
                class="w-full border-2 border-blue-200 text-blue-700 hover:bg-blue-50 font-semibold py-3.5 rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Signer le contrat
            </a>
            @endif
            @endif
            <a href="{{ route('home') }}"
                class="w-full text-gray-500 hover:text-gray-700 text-sm font-medium py-2 transition-colors">
                Retour à l'accueil
            </a>
        </div>
    </div>
</div>
@endsection
