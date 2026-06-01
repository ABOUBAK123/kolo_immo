@extends('layouts.app')
@section('title', 'Nos offres — Kolo Immo')
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Choisissez votre offre</h1>
        <p class="text-gray-500 text-lg">Publiez, gérez et développez votre portefeuille immobilier avec Kolo Immo.</p>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-{{ $plans->count() > 2 ? '3' : $plans->count() }} gap-6">
        @foreach($plans as $plan)
        @php $isCurrent = $currentPlan?->id === $plan->id; @endphp
        <div class="relative bg-white rounded-2xl border-2 {{ $plan->slug === 'pro' ? 'border-blue-600 shadow-lg shadow-blue-100' : 'border-gray-100 shadow-sm' }} p-7 flex flex-col">

            @if($plan->slug === 'pro')
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-bold px-4 py-1 rounded-full">
                Recommandé
            </div>
            @endif

            @if($isCurrent)
            <div class="absolute top-4 right-4 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                Plan actuel
            </div>
            @endif

            <div class="mb-5">
                <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $plan->name }}</h2>
                @if($plan->description)
                <p class="text-sm text-gray-500">{{ $plan->description }}</p>
                @endif
            </div>

            <div class="mb-6">
                @if($plan->price_monthly === 0)
                <p class="text-3xl font-extrabold text-gray-900">Gratuit</p>
                <p class="text-sm text-gray-400">Pour toujours</p>
                @else
                <p class="text-3xl font-extrabold text-gray-900">{{ number_format($plan->price_monthly, 0, ',', ' ') }} <span class="text-lg font-semibold text-gray-500">FCFA</span></p>
                <p class="text-sm text-gray-400">par mois</p>
                @endif
            </div>

            <ul class="space-y-3 text-sm mb-8 flex-1">
                <li class="flex items-center gap-2 text-gray-700">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>Jusqu'à <strong>{{ $plan->max_properties === 999 ? 'Illimité' : $plan->max_properties }}</strong> bien(s)</span>
                </li>
                <li class="flex items-center gap-2 text-gray-700">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span><strong>{{ $plan->max_photos_per_property }}</strong> photos par bien</span>
                </li>
                <li class="flex items-center gap-2 {{ $plan->featured_listing ? 'text-gray-700' : 'text-gray-300' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ $plan->featured_listing ? 'text-green-500' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>Mise en avant des annonces</span>
                </li>
                <li class="flex items-center gap-2 {{ $plan->analytics_advanced ? 'text-gray-700' : 'text-gray-300' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ $plan->analytics_advanced ? 'text-green-500' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>Statistiques avancées</span>
                </li>
                <li class="flex items-center gap-2 {{ $plan->priority_support ? 'text-gray-700' : 'text-gray-300' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ $plan->priority_support ? 'text-green-500' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>Support prioritaire</span>
                </li>
            </ul>

            @auth
                @if($isCurrent)
                <button disabled class="w-full py-3 rounded-xl border-2 border-gray-200 text-gray-400 font-semibold text-sm cursor-not-allowed">
                    Plan actuel
                </button>
                @else
                <form action="{{ route('subscriptions.subscribe') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button type="submit"
                        class="w-full py-3 rounded-xl font-bold text-sm transition-colors {{ $plan->slug === 'pro' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-900 hover:bg-black text-white' }}">
                        {{ $plan->price_monthly === 0 ? 'Choisir ce plan' : 'Souscrire — ' . number_format($plan->price_monthly, 0, ',', ' ') . ' FCFA' }}
                    </button>
                </form>
                @endif
            @else
            <a href="{{ route('register') }}"
                class="block w-full py-3 rounded-xl font-bold text-sm text-center transition-colors {{ $plan->slug === 'pro' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-900 hover:bg-black text-white' }}">
                S'inscrire gratuitement
            </a>
            @endauth
        </div>
        @endforeach
    </div>

    <p class="text-center text-sm text-gray-400 mt-8">
        Paiement sécurisé via Orange Money, Wave, MTN MoMo et Moov Money.
        Annulez à tout moment depuis votre espace.
    </p>
</div>
@endsection
