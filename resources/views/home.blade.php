@extends('layouts.app')

@section('title', 'Kolo Immo - Résidences meublées en Afrique de l\'Ouest')
@section('description', 'Trouvez et réservez des appartements, studios et villas meublés à Abidjan, Dakar, Ouagadougou, Bamako. Paiements sécurisés via Mobile Money.')

@section('content')

<!-- Hero Section -->
<section class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2E86C1 60%, #3498DB 100%);">
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <polygon points="0,100 100,0 100,100" fill="white"/>
        </svg>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 text-center">
        <div class="inline-flex items-center gap-2 bg-white bg-opacity-20 text-white text-sm font-medium px-4 py-1.5 rounded-full mb-6">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            500+ résidences disponibles dès maintenant
        </div>
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-4">
            Trouvez votre résidence idéale<br class="hidden md:block">
            <span class="text-yellow-300">en Afrique de l'Ouest</span>
        </h1>
        <p class="text-lg md:text-xl text-blue-100 mb-10 max-w-2xl mx-auto">
            Des appartements, studios et villas meublés pour tous vos séjours. Réservez en toute confiance avec nos garanties exclusives.
        </p>

        <!-- Search Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-4 md:p-6 max-w-4xl mx-auto">
            <form action="{{ route('properties.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-1">
                        <label class="block text-xs font-semibold text-gray-500 mb-1 text-left">DESTINATION</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <input type="text" name="city" placeholder="Abidjan, Dakar..."
                                class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 text-left">ARRIVÉE</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <input type="date" name="check_in" min="{{ date('Y-m-d') }}"
                                class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 text-left">DÉPART</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <input type="date" name="check_out" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 text-left">VOYAGEURS</label>
                        <select name="guests" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 bg-white">
                            <option value="1">1 voyageur</option>
                            <option value="2">2 voyageurs</option>
                            <option value="3">3 voyageurs</option>
                            <option value="4">4 voyageurs</option>
                            <option value="5">5+ voyageurs</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="w-full bg-orange-400 hover:bg-orange-500 text-white font-bold py-3.5 px-6 rounded-xl text-base transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Rechercher
                    </button>
                </div>
            </form>
        </div>

        <p class="text-blue-200 text-sm mt-4">
            Abidjan · Dakar · Ouagadougou · Bamako · Lomé · Cotonou
        </p>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Pourquoi choisir Kolo Immo ?</h2>
            <p class="text-gray-500 max-w-xl mx-auto">Une plateforme conçue pour la sécurité et la confiance en Afrique de l'Ouest</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6 rounded-2xl border border-gray-100 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #FEF3C7, #FCD34D);">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">Paiements Sécurisés</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Payez via Mobile Money Orange, Wave ou MTN. Vos fonds sont sécurisés en séquestre jusqu'à votre arrivée confirmée.</p>
                <div class="flex items-center justify-center gap-2 mt-4">
                    <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2.5 py-1 rounded-full">Orange Money</span>
                    <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">Wave</span>
                    <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full">MTN</span>
                </div>
            </div>

            <div class="text-center p-6 rounded-2xl border border-gray-100 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #DBEAFE, #93C5FD);">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">Contrats Numériques</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Chaque location génère automatiquement un contrat PDF signé électroniquement via OTP. Valeur juridique reconnue.</p>
                <div class="flex items-center justify-center gap-2 mt-4">
                    <span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">PDF Sécurisé</span>
                    <span class="bg-purple-100 text-purple-700 text-xs font-semibold px-2.5 py-1 rounded-full">Signature OTP</span>
                </div>
            </div>

            <div class="text-center p-6 rounded-2xl border border-gray-100 hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #D1FAE5, #6EE7B7);">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">Double Vérification KYC</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Propriétaires et locataires sont vérifiés avec CNI/Passeport. Zéro fraude grâce à notre système de vérification d'identité.</p>
                <div class="flex items-center justify-center gap-2 mt-4">
                    <span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">Propriétaires vérifiés</span>
                    <span class="bg-teal-100 text-teal-700 text-xs font-semibold px-2.5 py-1 rounded-full">0% fraude</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Cities -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Villes populaires</h2>
            <p class="text-gray-500">Des milliers de résidences vous attendent dans ces villes</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['name' => 'Abidjan', 'country' => 'Côte d\'Ivoire', 'count' => '180+', 'gradient' => 'from-orange-400 to-red-500'],
                ['name' => 'Dakar', 'country' => 'Sénégal', 'count' => '120+', 'gradient' => 'from-green-400 to-teal-600'],
                ['name' => 'Ouagadougou', 'country' => 'Burkina Faso', 'count' => '85+', 'gradient' => 'from-red-400 to-pink-600'],
                ['name' => 'Bamako', 'country' => 'Mali', 'count' => '70+', 'gradient' => 'from-yellow-400 to-orange-500'],
            ] as $city)
            <a href="{{ route('properties.index', ['city' => $city['name']]) }}"
                class="group relative rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer">
                <div class="h-40 md:h-52 bg-gradient-to-br {{ $city['gradient'] }}"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4">
                    <p class="text-white font-bold text-lg group-hover:text-yellow-300 transition-colors">{{ $city['name'] }}</p>
                    <p class="text-gray-300 text-xs">{{ $city['country'] }}</p>
                    <p class="text-yellow-300 text-xs font-semibold mt-1">{{ $city['count'] }} résidences</p>
                </div>
            </a>
            @endforeach
        </div>
        <div class="text-center mt-6">
            <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 text-blue-700 font-semibold hover:text-blue-900 transition-colors">
                Voir toutes les villes
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Comment ça marche ?</h2>
            <p class="text-gray-500">Réservez votre logement en 3 étapes simples</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <!-- Connector line (desktop) -->
            <div class="hidden md:block absolute top-12 left-1/4 right-1/4 h-0.5 bg-gradient-to-r from-blue-200 to-orange-200"></div>

            @foreach([
                ['step' => '1', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'title' => 'Cherchez', 'desc' => 'Entrez votre destination, vos dates et vos préférences. Parcourez des centaines de résidences meublées vérifiées.', 'color' => 'bg-blue-700'],
                ['step' => '2', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'title' => 'Réservez', 'desc' => 'Choisissez votre logement, envoyez une demande de réservation. Le propriétaire vous confirme en 24h.', 'color' => 'bg-accent-500'],
                ['step' => '3', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'title' => 'Payez en sécurité', 'desc' => 'Payez via Mobile Money. Vos fonds sont sécurisés en séquestre et libérés à votre arrivée confirmée.', 'color' => 'bg-green-600'],
            ] as $step)
            <div class="text-center relative">
                <div class="w-24 h-24 mx-auto mb-5 rounded-full {{ $step['color'] }} flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $step['icon'] }}"/>
                    </svg>
                </div>
                <div class="w-7 h-7 bg-gray-800 rounded-full flex items-center justify-center text-white text-sm font-bold mx-auto -mt-3 mb-4">{{ $step['step'] }}</div>
                <h3 class="font-bold text-gray-900 text-xl mb-2">{{ $step['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Stats Banner -->
<section class="py-12" style="background: linear-gradient(135deg, #1B4F72, #2E86C1);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @foreach([
                ['value' => '500+', 'label' => 'Propriétés disponibles'],
                ['value' => '6', 'label' => 'Pays d\'Afrique de l\'Ouest'],
                ['value' => '0%', 'label' => 'Taux de fraude'],
                ['value' => '95%', 'label' => 'Contrats signés numériquement'],
            ] as $stat)
            <div>
                <p class="text-4xl md:text-5xl font-bold text-yellow-300 mb-2">{{ $stat['value'] }}</p>
                <p class="text-blue-100 text-sm font-medium">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Ce que disent nos utilisateurs</h2>
            <p class="text-gray-500">Des milliers de locataires et propriétaires nous font confiance</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
                ['name' => 'Aminata Koné', 'city' => 'Abidjan', 'rating' => 5, 'text' => 'Excellent service ! J\'ai trouvé un appartement meublé en 30 minutes. Le paiement via Wave était super simple et sécurisé. Je recommande vivement !', 'type' => 'Locataire'],
                ['name' => 'Ousmane Diallo', 'city' => 'Dakar', 'rating' => 5, 'text' => 'En tant que propriétaire, Kolo Immo m\'a permis de rentabiliser mon appartement facilement. Les contrats numériques évitent tous les problèmes habituels.', 'type' => 'Propriétaire'],
                ['name' => 'Fatou Traoré', 'city' => 'Ouagadougou', 'rating' => 4, 'text' => 'J\'utilise Kolo Immo pour mes voyages professionnels. La vérification KYC me rassure et je trouve toujours des logements propres et bien équipés.', 'type' => 'Locataire'],
            ] as $testimonial)
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center gap-1 mb-4">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-4 h-4 {{ $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">"{{ $testimonial['text'] }}"</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                        {{ substr($testimonial['name'], 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ $testimonial['name'] }}</p>
                        <p class="text-gray-400 text-xs">{{ $testimonial['type'] }} · {{ $testimonial['city'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA for Owners -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-gradient-to-br from-primary-700 to-blue-600 rounded-3xl p-8 md:p-12 text-white shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-white opacity-5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            <div class="relative">
                <div class="w-16 h-16 bg-yellow-400 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold mb-3">Vous êtes propriétaire ?</h2>
                <p class="text-blue-100 text-lg mb-2">Rentabilisez votre appartement, studio ou villa meublée.</p>
                <p class="text-blue-200 text-sm mb-8">Publiez gratuitement · Paiements garantis · Contrats automatiques · Assistance 24/7</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register', ['role' => 'owner']) }}" class="bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-bold py-3.5 px-8 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                        Commencer à louer
                    </a>
                    <a href="#" class="text-white border-2 border-white border-opacity-40 hover:border-opacity-80 font-semibold py-3.5 px-8 rounded-xl transition-all duration-200">
                        En savoir plus
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
