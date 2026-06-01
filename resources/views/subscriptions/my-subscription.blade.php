@extends('layouts.app')
@section('title', 'Mon abonnement — Kolo Immo')
@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Mon abonnement</h1>
        <p class="text-gray-500 text-sm mt-1">Gérez votre offre Kolo Immo</p>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if($subscription && $subscription->isActive())
    <div class="bg-white rounded-2xl border-2 border-blue-200 shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="inline-block bg-green-100 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full mb-2">Actif</span>
                <h2 class="text-xl font-bold text-gray-900">{{ $subscription->plan->name }}</h2>
            </div>
            <p class="text-2xl font-extrabold text-gray-900">{{ $subscription->plan->formattedPrice() }}</p>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 border-t border-gray-100 pt-4">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Début</p>
                <p class="font-semibold">{{ $subscription->starts_at->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Renouvellement</p>
                <p class="font-semibold">{{ $subscription->ends_at->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Biens autorisés</p>
                <p class="font-semibold">{{ $subscription->plan->max_properties === 999 ? 'Illimité' : $subscription->plan->max_properties }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Jours restants</p>
                <p class="font-semibold">{{ now()->diffInDays($subscription->ends_at) }} jours</p>
            </div>
        </div>

        @if(!$subscription->plan->isFree())
        <div class="mt-4 pt-4 border-t border-gray-100">
            <form action="{{ route('subscriptions.cancel') }}" method="POST"
                onsubmit="return confirm('Annuler votre abonnement ? Il restera actif jusqu\'au {{ $subscription->ends_at->format(\'d/m/Y\') }}.')">
                @csrf
                <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">
                    Annuler l'abonnement
                </button>
            </form>
        </div>
        @endif
    </div>
    @else
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6 text-center">
        <p class="text-amber-800 font-semibold mb-2">Aucun abonnement actif</p>
        <p class="text-sm text-amber-600 mb-4">Choisissez un plan pour accéder à toutes les fonctionnalités propriétaire.</p>
        <a href="{{ route('subscriptions.plans') }}" class="inline-block bg-blue-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm hover:bg-blue-700 transition">
            Voir les offres
        </a>
    </div>
    @endif

    <!-- Upgrade options -->
    @if(!($subscription && $subscription->isActive() && $subscription->plan->slug === 'premium'))
    <h3 class="font-bold text-gray-800 mb-4">Autres offres disponibles</h3>
    <div class="space-y-3">
        @foreach($plans as $plan)
        @if(!($subscription?->plan_id === $plan->id && $subscription->isActive()))
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="font-semibold text-gray-900">{{ $plan->name }}</p>
                <p class="text-xs text-gray-400">{{ $plan->max_properties === 999 ? 'Biens illimités' : $plan->max_properties . ' bien(s) max' }} · {{ $plan->max_photos_per_property }} photos/bien</p>
            </div>
            <div class="flex items-center gap-3">
                <p class="font-bold text-gray-900 text-sm">{{ $plan->formattedPrice() }}</p>
                <form action="{{ route('subscriptions.subscribe') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button type="submit" class="text-xs px-3 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                        Choisir
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @endif
</div>
@endsection
