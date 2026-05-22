@extends('layouts.app')

@section('title', 'Résidences meublées' . (request('city') ? ' à ' . request('city') : '') . ' - Kolo Immo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ filtersOpen: false }">

    <!-- Search bar sticky -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form action="{{ route('properties.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="block text-xs font-semibold text-gray-500 mb-1">DESTINATION</label>
                <input type="text" name="city" value="{{ request('city') }}" placeholder="Ville ou quartier..."
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="min-w-32">
                <label class="block text-xs font-semibold text-gray-500 mb-1">ARRIVÉE</label>
                <input type="date" name="check_in" value="{{ request('check_in') }}"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="min-w-32">
                <label class="block text-xs font-semibold text-gray-500 mb-1">DÉPART</label>
                <input type="date" name="check_out" value="{{ request('check_out') }}"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="min-w-28">
                <label class="block text-xs font-semibold text-gray-500 mb-1">VOYAGEURS</label>
                <select name="guests" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}" {{ request('guests') == $i ? 'selected' : '' }}>{{ $i }} {{ $i > 1 ? 'pers.' : 'pers.' }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="bg-orange-400 hover:bg-orange-500 text-white font-semibold px-6 py-2 rounded-lg text-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Rechercher
            </button>
        </form>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Filters Sidebar -->
        <aside class="lg:w-72 flex-shrink-0">
            <!-- Mobile filters toggle -->
            <button @click="filtersOpen = !filtersOpen" class="lg:hidden w-full flex items-center justify-between bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 mb-4">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtres
                </span>
                <svg class="w-4 h-4 transition-transform" :class="filtersOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <form action="{{ route('properties.index') }}" method="GET" id="filters-form"
                class="bg-white rounded-2xl border border-gray-100 p-5 space-y-6"
                :class="{ 'hidden lg:block': !filtersOpen, 'block': filtersOpen }">

                <input type="hidden" name="city" value="{{ request('city') }}">
                <input type="hidden" name="check_in" value="{{ request('check_in') }}">
                <input type="hidden" name="check_out" value="{{ request('check_out') }}">
                <input type="hidden" name="guests" value="{{ request('guests') }}">

                <!-- Price Range -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Prix par nuit</h3>
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <label class="text-xs text-gray-500 mb-1 block">Min (FCFA)</label>
                            <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="0"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="text-gray-400 mt-5">—</div>
                        <div class="flex-1">
                            <label class="text-xs text-gray-500 mb-1 block">Max (FCFA)</label>
                            <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="500 000"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Type -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Type de logement</h3>
                    <div class="space-y-2">
                        @foreach(['studio' => 'Studio', 'appartement' => 'Appartement', 'villa' => 'Villa', 'chambre' => 'Chambre', 'duplex' => 'Duplex'] as $value => $label)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="types[]" value="{{ $value }}"
                                {{ in_array($value, request('types', [])) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Amenities -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Équipements</h3>
                    <div class="space-y-2">
                        @foreach(['wifi' => 'Wi-Fi', 'climatisation' => 'Climatisation', 'parking' => 'Parking', 'piscine' => 'Piscine', 'cuisine' => 'Cuisine équipée', 'television' => 'Télévision', 'machine_a_laver' => 'Machine à laver', 'gardien' => 'Gardien 24h/24'] as $value => $label)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="amenities[]" value="{{ $value }}"
                                {{ in_array($value, request('amenities', [])) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Min Rating -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Note minimale</h3>
                    <div class="flex items-center gap-2">
                        @foreach([4 => '4+', 3 => '3+', 2 => '2+'] as $value => $label)
                        <label class="flex-1">
                            <input type="radio" name="rating_min" value="{{ $value }}"
                                {{ request('rating_min') == $value ? 'checked' : '' }} class="sr-only peer">
                            <div class="text-center text-sm py-2 border border-gray-200 rounded-lg cursor-pointer peer-checked:bg-blue-700 peer-checked:text-white peer-checked:border-blue-700 hover:border-blue-300 transition-colors">
                                <span class="text-yellow-400">★</span> {{ $label }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Booking type -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-3">Mode de réservation</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="booking_type" value="" {{ !request('booking_type') ? 'checked' : '' }} class="w-4 h-4 border-gray-300 text-blue-700 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Tous</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="booking_type" value="instant" {{ request('booking_type') === 'instant' ? 'checked' : '' }} class="w-4 h-4 border-gray-300 text-blue-700 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Réservation instantanée</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="booking_type" value="request" {{ request('booking_type') === 'request' ? 'checked' : '' }} class="w-4 h-4 border-gray-300 text-blue-700 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Sur demande</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                        Appliquer
                    </button>
                    <a href="{{ route('properties.index') }}" class="flex-1 text-center border border-gray-200 text-gray-600 font-semibold py-2.5 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </aside>

        <!-- Results -->
        <div class="flex-1 min-w-0">
            <!-- Results header -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        @if(isset($properties) && $properties->total() > 0)
                            {{ $properties->total() }} résidence{{ $properties->total() > 1 ? 's' : '' }} trouvée{{ $properties->total() > 1 ? 's' : '' }}
                            @if(request('city')) <span class="text-blue-700">à {{ request('city') }}</span>@endif
                        @else
                            Toutes les résidences
                        @endif
                    </h1>
                    @if(request('check_in') && request('check_out'))
                    <p class="text-gray-500 text-sm">Du {{ \Carbon\Carbon::parse(request('check_in'))->format('d/m/Y') }} au {{ \Carbon\Carbon::parse(request('check_out'))->format('d/m/Y') }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Trier par:</span>
                    <form method="GET" action="{{ route('properties.index') }}">
                        @foreach(request()->except('sort') as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                @endforeach
                            @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <select name="sort" onchange="this.form.submit()"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700">
                            <option value="popularity" {{ request('sort') === 'popularity' ? 'selected' : '' }}>Popularité</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                            <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Mieux notés</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Les plus récents</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Property Grid -->
            @if(isset($properties) && $properties->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($properties as $property)
                @include('partials.property-card', ['property' => $property])
                @endforeach
            </div>

            <!-- Pagination -->
            @if($properties->hasPages())
            <div class="mt-8">
                {{ $properties->withQueryString()->links() }}
            </div>
            @endif

            @else
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Aucune résidence trouvée</h3>
                <p class="text-gray-500 mb-6 max-w-sm">
                    Aucune résidence ne correspond à votre recherche. Essayez d'élargir vos critères ou de changer de ville.
                </p>
                <div class="flex flex-wrap gap-3 justify-center">
                    <a href="{{ route('properties.index') }}" class="bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-blue-800 transition-colors">
                        Voir toutes les résidences
                    </a>
                    <a href="{{ route('home') }}" class="border border-gray-200 text-gray-700 font-semibold px-6 py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                        Retour à l'accueil
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
