@extends('layouts.app')

@section('title', 'Paiement échoué - Kolo Immo')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">

        <!-- Error icon -->
        <div class="relative inline-block mb-8">
            <div class="w-32 h-32 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                <div class="w-24 h-24 bg-red-200 rounded-full flex items-center justify-center">
                    <svg class="w-14 h-14 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Paiement échoué</h1>
        <p class="text-gray-500 text-lg mb-6">Votre paiement n'a pas pu être traité</p>

        @if(session('error_message') || isset($errorMessage))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-left mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold text-red-800 text-sm mb-1">Erreur de paiement</p>
                    <p class="text-red-700 text-sm">{{ session('error_message') ?? $errorMessage ?? 'Une erreur est survenue lors du traitement du paiement.' }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Common reasons -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-left mb-6">
            <h3 class="font-bold text-gray-900 mb-3 text-sm">Causes possibles :</h3>
            <ul class="space-y-2">
                @foreach([
                    'Solde insuffisant sur votre compte Mobile Money',
                    'Numéro de téléphone incorrect ou invalide',
                    'Transaction annulée ou expirée (60 secondes)',
                    'Problème de réseau temporaire',
                    'Limite de transaction journalière atteinte',
                ] as $reason)
                <li class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ $reason }}
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Action buttons -->
        <div class="flex flex-col gap-3">
            @if(isset($booking))
            <a href="{{ route('payments.initiate', $booking) }}"
                class="w-full bg-orange-400 hover:bg-orange-500 text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-lg flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Réessayer le paiement
            </a>
            <a href="{{ route('bookings.show', $booking) }}"
                class="w-full border-2 border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold py-3.5 rounded-xl transition-all duration-200">
                Voir ma réservation
            </a>
            @else
            <a href="{{ route('home') }}"
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3.5 rounded-xl transition-all duration-200">
                Retour à l'accueil
            </a>
            @endif
            <a href="#" class="text-sm text-gray-400 hover:text-gray-600 py-2">
                Contacter le support
            </a>
        </div>

        <!-- Tips -->
        <div class="mt-6 p-4 bg-blue-50 rounded-xl text-left border border-blue-200">
            <p class="text-sm font-semibold text-blue-800 mb-2">Conseils pour réussir votre paiement :</p>
            <ul class="text-xs text-blue-700 space-y-1">
                <li>• Vérifiez votre solde avant de payer</li>
                <li>• Confirmez la demande de paiement dès réception du SMS</li>
                <li>• Assurez-vous d'avoir du réseau sur votre téléphone</li>
            </ul>
        </div>
    </div>
</div>
@endsection
