@extends('layouts.app')

@section('title', $property->title . ' - Kolo Immo')
@section('description', Str::limit($property->description, 155))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    selectedDates: { check_in: '{{ request('check_in') }}', check_out: '{{ request('check_out') }}' },
    guests: {{ request('guests', 1) }},
    descExpanded: false,
    lightbox: false,
    lightboxSrc: '',
    openLightbox(src) { this.lightboxSrc = src; this.lightbox = true; },
    get nights() {
        if (!this.selectedDates.check_in || !this.selectedDates.check_out) return 0;
        const d1 = new Date(this.selectedDates.check_in), d2 = new Date(this.selectedDates.check_out);
        return Math.max(0, Math.round((d2 - d1) / 86400000));
    },
    get subtotal() { return this.nights * {{ $property->price_per_night }}; },
    get serviceFee() { return Math.round(this.subtotal * 0.03); },
    get total() { return this.subtotal + this.serviceFee; }
}">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-700">Accueil</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('properties.index', ['city' => $property->city]) }}" class="hover:text-blue-700">{{ $property->city }}</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-800 font-medium line-clamp-1">{{ $property->title }}</span>
    </nav>

    <!-- Photo Gallery -->
    <div class="grid grid-cols-4 grid-rows-2 gap-2 rounded-2xl overflow-hidden mb-8 h-64 md:h-96">
        <!-- Main photo -->
        <div class="col-span-4 md:col-span-2 row-span-2 cursor-pointer" @click="openLightbox('{{ $property->photos->first() ? asset('storage/'.$property->photos->first()->path) : '' }}')">
            @if($property->photos->first())
            <img src="{{ asset('storage/' . $property->photos->first()->path) }}" alt="{{ $property->title }}"
                class="w-full h-full object-cover hover:brightness-90 transition-all">
            @else
            <div class="w-full h-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center">
                <svg class="w-16 h-16 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
            </div>
            @endif
        </div>
        <!-- Thumbnails -->
        @foreach($property->photos->skip(1)->take(4) as $photo)
        <div class="hidden md:block cursor-pointer" @click="openLightbox('{{ asset('storage/'.$photo->path) }}')">
            <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $property->title }}"
                class="w-full h-full object-cover hover:brightness-90 transition-all">
        </div>
        @endforeach
        @for($i = $property->photos->count() - 1; $i < 4; $i++)
        <div class="hidden md:block bg-gradient-to-br from-gray-200 to-gray-300"></div>
        @endfor
    </div>

    <!-- Lightbox -->
    <div x-show="lightbox" x-cloak @click.self="lightbox = false" @keydown.escape.window="lightbox = false"
        class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center p-4">
        <button @click="lightbox = false" class="absolute top-4 right-4 text-white hover:text-gray-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img :src="lightboxSrc" class="max-w-full max-h-full rounded-lg object-contain">
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left column -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Property header -->
            <div>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full capitalize">{{ $property->type ?? 'Appartement' }}</span>
                            @if($property->booking_type === 'instant')
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">⚡ Réservation instantanée</span>
                            @endif
                        </div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ $property->title }}</h1>
                        <div class="flex items-center gap-2 text-gray-500">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $property->district ? $property->district.', ' : '' }}{{ $property->city }}{{ $property->country ? ', '.$property->country : '' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="font-bold text-gray-900">{{ number_format($property->average_rating ?? 0, 1) }}</span>
                            <span class="text-gray-400 text-sm">({{ $property->reviews_count ?? 0 }} avis)</span>
                        </div>
                    </div>
                </div>

                <!-- Owner info -->
                <div class="flex items-center gap-3 mt-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-12 h-12 rounded-full flex-shrink-0 flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                        {{ substr($property->owner->prenom ?? $property->owner->name ?? 'P', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Logement proposé par</p>
                        <p class="font-semibold text-gray-900">{{ $property->owner->prenom ?? $property->owner->name ?? 'Propriétaire' }}</p>
                    </div>
                    @if($property->owner->kyc_verified ?? false)
                    <span class="ml-auto flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Vérifié KYC
                    </span>
                    @endif
                </div>
            </div>

            <!-- Quick info bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-6 border-y border-gray-100">
                @foreach([
                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Capacité', 'value' => ($property->capacity ?? 1).' personnes'],
                    ['icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Chambres', 'value' => ($property->bedrooms ?? 1).' chambre(s)'],
                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'Salles de bain', 'value' => ($property->bathrooms ?? 1).' salle(s)'],
                    ['icon' => 'M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4', 'label' => 'Superficie', 'value' => ($property->area ?? '?').' m²'],
                ] as $info)
                <div class="text-center p-3 rounded-xl bg-gray-50">
                    <svg class="w-6 h-6 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $info['icon'] }}"/>
                    </svg>
                    <p class="font-semibold text-gray-900 text-sm">{{ $info['value'] }}</p>
                    <p class="text-gray-400 text-xs">{{ $info['label'] }}</p>
                </div>
                @endforeach
            </div>

            <!-- Description -->
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-3">Description</h2>
                <div class="text-gray-600 leading-relaxed text-sm"
                    :class="descExpanded ? '' : 'line-clamp-4'">
                    {{ $property->description }}
                </div>
                <button @click="descExpanded = !descExpanded" class="text-blue-700 text-sm font-semibold mt-2 hover:text-blue-900">
                    <span x-text="descExpanded ? 'Lire moins' : 'Lire plus'"></span>
                </button>
            </div>

            <!-- Amenities -->
            @if($property->amenities && $property->amenities->isNotEmpty())
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Équipements</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @php
                    $amenityIcons = [
                        'wifi' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0',
                        'climatisation' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2',
                        'default' => 'M5 13l4 4L19 7',
                    ];
                    $amenityLabels = [
                        'wifi' => 'Wi-Fi', 'climatisation' => 'Climatisation', 'parking' => 'Parking',
                        'piscine' => 'Piscine', 'cuisine' => 'Cuisine équipée', 'television' => 'Télévision',
                        'machine_a_laver' => 'Machine à laver', 'gardien' => 'Gardien 24h/24',
                        'ascenseur' => 'Ascenseur', 'generateur' => 'Groupe électrogène',
                        'eau_chaude' => 'Eau chaude', 'balcon' => 'Balcon/Terrasse',
                    ];
                    @endphp
                    @foreach($property->amenities as $amenity)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $amenityIcons[$amenity->amenity] ?? $amenityIcons['default'] }}"/>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-700 font-medium">{{ $amenityLabels[$amenity->amenity] ?? ucfirst(str_replace('_', ' ', $amenity->amenity)) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- House Rules -->
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Règles de la maison</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach([
                        ['key' => 'pets_allowed', 'label' => 'Animaux de compagnie', 'icon' => '🐾'],
                        ['key' => 'smoking_allowed', 'label' => 'Fumeur autorisé', 'icon' => '🚬'],
                        ['key' => 'parties_allowed', 'label' => 'Fêtes autorisées', 'icon' => '🎉'],
                    ] as $rule)
                    <div class="flex items-center gap-3 p-3 rounded-xl {{ ($property->{$rule['key']} ?? false) ? 'bg-green-50' : 'bg-red-50' }}">
                        <span class="text-xl">{{ $rule['icon'] }}</span>
                        <span class="text-sm font-medium {{ ($property->{$rule['key']} ?? false) ? 'text-green-800' : 'text-red-700' }}">
                            {{ $rule['label'] }}: {{ ($property->{$rule['key']} ?? false) ? 'Autorisé' : 'Non autorisé' }}
                        </span>
                    </div>
                    @endforeach
                    @if($property->check_in_time)
                    <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl">
                        <span class="text-xl">🕐</span>
                        <span class="text-sm font-medium text-blue-800">Check-in: {{ $property->check_in_time }} — Check-out: {{ $property->check_out_time }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Location -->
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Localisation</h2>
                <p class="text-gray-600 text-sm mb-3">{{ $property->address }}, {{ $property->district }}, {{ $property->city }}</p>
                <div class="w-full h-64 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center border border-blue-200">
                    <div class="text-center">
                        <svg class="w-10 h-10 text-blue-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-blue-700 font-medium">{{ $property->city }}</p>
                        @if($property->latitude && $property->longitude)
                        <a href="https://maps.google.com/?q={{ $property->latitude }},{{ $property->longitude }}" target="_blank"
                            class="text-sm text-blue-600 underline mt-1 block">Voir sur Google Maps</a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Reviews -->
            @if($property->reviews && $property->reviews->count() > 0)
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        Avis ({{ $property->reviews->count() }})
                    </h2>
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-2xl font-bold text-gray-900">{{ number_format($property->average_rating ?? 0, 1) }}</span>
                        <span class="text-gray-500 text-sm">/ 5</span>
                    </div>
                </div>

                <div class="space-y-6">
                    @foreach($property->reviews->take(5) as $review)
                    <div class="border-b border-gray-100 pb-6 last:border-0">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                                    {{ substr($review->tenant->prenom ?? $review->tenant->name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $review->tenant->prenom ?? $review->tenant->name ?? 'Anonyme' }}</p>
                                    <p class="text-gray-400 text-xs">{{ $review->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->overall_rating ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @endfor
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $review->comment }}</p>
                        @if($review->owner_reply)
                        <div class="mt-3 ml-4 p-3 bg-gray-50 rounded-xl border-l-4 border-blue-200">
                            <p class="text-xs font-semibold text-gray-700 mb-1">Réponse du propriétaire:</p>
                            <p class="text-sm text-gray-600">{{ $review->owner_reply }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Right column: Booking Widget (sticky) -->
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                    <!-- Price -->
                    <div class="flex items-baseline gap-2 mb-6">
                        <span class="text-3xl font-bold text-gray-900">{{ number_format($property->price_per_night, 0, ',', ' ') }} FCFA</span>
                        <span class="text-gray-500">/ nuit</span>
                    </div>

                    <!-- Availability check -->
                    <div x-show="selectedDates.check_in && selectedDates.check_out && nights > 0"
                        class="mb-3 p-3 bg-green-50 rounded-xl border border-green-200 text-green-700 text-sm font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span x-text="'Disponible - ' + nights + ' nuit(s)'"></span>
                    </div>

                    <!-- Booking form -->
                    <form action="{{ route('bookings.create') }}" method="GET">
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        <!-- Dates -->
                        <div class="border border-gray-200 rounded-xl overflow-hidden mb-3">
                            <div class="grid grid-cols-2">
                                <div class="p-3 border-r border-gray-200">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">ARRIVÉE</label>
                                    <input type="date" name="check_in" x-model="selectedDates.check_in" min="{{ date('Y-m-d') }}"
                                        class="w-full text-sm text-gray-800 focus:outline-none" required>
                                </div>
                                <div class="p-3">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">DÉPART</label>
                                    <input type="date" name="check_out" x-model="selectedDates.check_out"
                                        class="w-full text-sm text-gray-800 focus:outline-none" required>
                                </div>
                            </div>
                        </div>

                        <!-- Guests -->
                        <div class="border border-gray-200 rounded-xl p-3 mb-4">
                            <label class="block text-xs font-bold text-gray-700 mb-1">VOYAGEURS</label>
                            <select name="guests" x-model="guests" class="w-full text-sm text-gray-800 focus:outline-none bg-transparent">
                                @for($i = 1; $i <= ($property->capacity ?? 10); $i++)
                                <option value="{{ $i }}">{{ $i }} {{ $i > 1 ? 'voyageurs' : 'voyageur' }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Price breakdown -->
                        <div x-show="nights > 0" class="space-y-2 mb-4 border-t border-gray-100 pt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ number_format($property->price_per_night, 0, ',', ' ') }} FCFA × <span x-text="nights"></span> nuit(s)</span>
                                <span class="text-gray-900 font-medium" x-text="subtotal.toLocaleString('fr-FR') + ' FCFA'"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Frais de service (3%)</span>
                                <span class="text-gray-900 font-medium" x-text="serviceFee.toLocaleString('fr-FR') + ' FCFA'"></span>
                            </div>
                            @if($property->deposit_amount)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Caution (remboursable)</span>
                                <span class="text-gray-900 font-medium">{{ number_format($property->deposit_amount, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                            <div class="flex justify-between font-bold border-t border-gray-200 pt-2 mt-2">
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900" x-text="total.toLocaleString('fr-FR') + ' FCFA'"></span>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-orange-400 hover:bg-orange-500 text-white font-bold py-3.5 rounded-xl text-base transition-all duration-200 shadow-lg hover:shadow-xl">
                            Réserver maintenant
                        </button>
                    </form>

                    <!-- Cancellation policy -->
                    <div class="mt-4 flex items-center gap-2 text-sm text-gray-500 text-center justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Politique d'annulation: <strong class="text-gray-700">{{ ucfirst($property->cancellation_policy ?? 'flexible') }}</strong></span>
                    </div>

                    <!-- Contact owner -->
                    @auth
                    <a href="{{ route('messages.index') }}?property={{ $property->id }}&owner={{ $property->owner_id }}"
                        class="mt-3 w-full border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold py-3 rounded-xl text-sm text-center flex items-center justify-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        Contacter le propriétaire
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Similar Properties -->
    @if(isset($similarProperties) && $similarProperties->count() > 0)
    <div class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Résidences similaires à {{ $property->city }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($similarProperties as $similar)
            @include('partials.property-card', ['property' => $similar])
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
