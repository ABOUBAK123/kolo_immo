@extends('layouts.app')

@section('title', 'Mes logements - Kolo Immo')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mes logements</h1>
            <p class="text-gray-500 text-sm mt-0.5">Gérez vos biens et leur disponibilité</p>
        </div>
        <a href="{{ route('owner.properties.create') }}"
           class="flex items-center gap-2 bg-blue-700 text-white font-semibold px-4 py-2 rounded-xl text-sm hover:bg-blue-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter un logement
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if(session('warning'))
    <div class="mb-4 bg-yellow-50 border border-yellow-300 rounded-xl p-4 text-sm text-yellow-800">
        {!! session('warning') !!}
    </div>
    @endif

    @if(session('kyc_required'))
    <div class="mb-4 bg-orange-50 border border-orange-300 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <div>
            <p class="font-semibold text-orange-800 mb-1">Vérification d'identité requise</p>
            <p class="text-orange-700 text-sm">Pour publier plusieurs biens, vous devez d'abord valider votre identité (KYC).</p>
            <a href="{{ session('kyc_required') }}" class="inline-block mt-2 text-sm font-semibold text-orange-700 underline hover:text-orange-900">
                Vérifier mon identité →
            </a>
        </div>
    </div>
    @endif

    @forelse($properties as $property)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4 overflow-hidden">
        <div class="flex flex-col sm:flex-row">
            @if($property->cover_photo)
            <img src="{{ Storage::url($property->cover_photo) }}" alt="{{ $property->title }}"
                 class="w-full sm:w-40 h-32 sm:h-auto object-cover flex-shrink-0">
            @else
            <div class="w-full sm:w-40 h-32 bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
            </div>
            @endif

            <div class="flex-1 p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="font-bold text-gray-900 text-lg">{{ $property->title }}</h2>
                            @php
                            $statusClass = ['draft' => 'bg-gray-100 text-gray-600', 'active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-yellow-100 text-yellow-700', 'suspended' => 'bg-red-100 text-red-700'][$property->status] ?? 'bg-gray-100 text-gray-600';
                            $statusLabel = ['draft' => 'Brouillon', 'active' => 'Actif', 'inactive' => 'Inactif', 'suspended' => 'Suspendu'][$property->status] ?? $property->status;
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if($property->is_featured)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">⭐ En avant</span>
                            @endif
                        </div>
                        <p class="text-gray-500 text-sm">{{ $property->city }}, {{ $property->country }} · {{ ucfirst(str_replace('_', ' ', $property->type)) }}</p>
                        <p class="text-gray-700 font-semibold mt-1">{{ number_format($property->price_per_night, 0, ',', ' ') }} FCFA / nuit</p>
                    </div>
                    <div class="flex gap-3 text-center text-sm flex-shrink-0">
                        <div>
                            <p class="font-bold text-gray-900 text-lg">{{ $property->bookings_count }}</p>
                            <p class="text-gray-400 text-xs">Réservations</p>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 text-lg">{{ $property->reviews_count }}</p>
                            <p class="text-gray-400 text-xs">Avis</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 mt-4">
                    <a href="{{ route('owner.properties.edit', $property) }}"
                       class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                        Modifier
                    </a>
                    <a href="{{ route('properties.show', $property) }}"
                       class="px-3 py-1.5 bg-gray-50 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-100 transition" target="_blank">
                        Voir l'annonce
                    </a>
                    <a href="{{ route('owner.properties.availability', $property) }}"
                       class="px-3 py-1.5 bg-gray-50 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-100 transition">
                        Disponibilités
                    </a>
                    @if($property->status !== 'suspended')
                    <form method="POST" action="{{ route('owner.properties.toggle-status', $property) }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                {{ $property->status === 'active' ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                            {{ $property->status === 'active' ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('owner.properties.destroy', $property) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-semibold hover:bg-red-100 transition"
                            onclick="return confirm('Supprimer ce logement définitivement ?')">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Aucun logement</h3>
        <p class="text-gray-500 text-sm mb-6">Commencez par ajouter votre premier logement.</p>
        <a href="{{ route('owner.properties.create') }}"
           class="inline-flex items-center gap-2 bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm hover:bg-blue-800 transition-colors">
            Ajouter un logement
        </a>
    </div>
    @endforelse

    <div class="mt-4">{{ $properties->links() }}</div>
</div>
@endsection
