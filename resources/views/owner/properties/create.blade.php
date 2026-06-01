@extends('layouts.app')

@section('title', 'Publier un bien - Kolo Immo')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{
        step: 1,
        totalSteps: 7,
        fileList: [],
        amenities: {{ json_encode(old('amenities', [])) }},
        type: '{{ old('type', '') }}',
        previewPhotos: [],

        nextStep() { if(this.step < this.totalSteps) this.step++; },
        prevStep() { if(this.step > 1) this.step--; },
        setType(t) { this.type = t; },
        toggleAmenity(a) {
            if(this.amenities.includes(a)) {
                this.amenities = this.amenities.filter(x => x !== a);
            } else {
                this.amenities.push(a);
            }
        },
        handlePhotos(event) {
            const existingNames = this.fileList.map(f => f.name);
            Array.from(event.target.files).forEach(f => {
                if (!existingNames.includes(f.name)) this.fileList.push(f);
            });
            this.applyFilesToInput();
            this.rebuildPreviews();
        },
        removePhoto(index) {
            this.fileList.splice(index, 1);
            this.applyFilesToInput();
            this.rebuildPreviews();
        },
        applyFilesToInput() {
            const dt = new DataTransfer();
            this.fileList.forEach(f => dt.items.add(f));
            this.$refs.photoInput.files = dt.files;
        },
        rebuildPreviews() {
            this.previewPhotos = [];
            this.fileList.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => { this.previewPhotos.push(e.target.result); };
                reader.readAsDataURL(file);
            });
        }
    }">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Publier un bien</h1>
        <p class="text-gray-500">Complétez les informations pour mettre votre logement en location</p>
    </div>

    <!-- Step indicator -->
    <div class="mb-8">
        <div class="flex items-center gap-2 overflow-x-auto pb-2">
            @foreach(['Type', 'Localisation', 'Détails', 'Tarifs', 'Équipements', 'Règles', 'Photos'] as $i => $label)
            <div class="flex items-center gap-2 flex-shrink-0">
                <div class="flex items-center gap-1.5">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                        :class="{{ $i + 1 }} < step ? 'bg-green-500 text-white' : ({{ $i + 1 }} === step ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-400')">
                        <template x-if="{{ $i + 1 }} < step">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="{{ $i + 1 }} >= step">
                            <span>{{ $i + 1 }}</span>
                        </template>
                    </div>
                    <span class="text-xs font-medium hidden sm:block"
                        :class="{{ $i + 1 }} === step ? 'text-blue-700' : '{{ $i + 1 }} < step ? text-green-600 : text-gray-400'">
                        {{ $label }}
                    </span>
                </div>
                @if($i < 6)
                <div class="w-6 h-0.5 flex-shrink-0" :class="{{ $i + 1 }} < step ? 'bg-green-300' : 'bg-gray-200'"></div>
                @endif
            </div>
            @endforeach
        </div>
        <!-- Progress bar -->
        <div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-blue-700 rounded-full transition-all duration-300"
                :style="'width: ' + ((step - 1) / (totalSteps - 1) * 100) + '%'"></div>
        </div>
    </div>

    <form action="{{ route('owner.properties.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- STEP 1: Type & Basic Info -->
        <div x-show="step === 1" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Type et présentation</h2>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Type de logement</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach(['studio' => 'Studio', 'appartement' => 'Appartement', 'villa' => 'Villa', 'chambre' => 'Chambre', 'duplex' => 'Duplex', 'maison' => 'Maison'] as $val => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $val }}" class="sr-only peer"
                            @change="setType('{{ $val }}')" {{ old('type') === $val ? 'checked' : '' }}>
                        <div class="p-3 border-2 border-gray-200 rounded-xl text-center transition-all
                            peer-checked:border-blue-700 peer-checked:bg-blue-50 hover:border-blue-200"
                            :class="type === '{{ $val }}' ? 'border-blue-700 bg-blue-50' : ''">
                            <p class="font-semibold text-sm" :class="type === '{{ $val }}' ? 'text-blue-700' : 'text-gray-700'">{{ $label }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Titre de l'annonce</label>
                <input type="text" name="title" value="{{ old('title') }}"
                    placeholder="Ex: Bel appartement meublé à Cocody, Abidjan"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('title') ? 'border-red-300' : '' }}"
                    maxlength="100" required>
                <p class="text-xs text-gray-400 mt-1">Max 100 caractères</p>
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description détaillée</label>
                <textarea name="description" rows="5" placeholder="Décrivez votre logement en détail : espace, décoration, quartier, transports à proximité..."
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none {{ $errors->has('description') ? 'border-red-300' : '' }}"
                    required>{{ old('description') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Min 100 caractères recommandés</p>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <!-- STEP 2: Location -->
        <div x-show="step === 2" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Localisation</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pays</label>
                    <select name="country" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Sélectionner un pays...</option>
                        @foreach(\App\Helpers\WestAfrica::countries() as $name => $capital)
                        <option value="{{ $name }}" {{ old('country') === $name ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ville</label>
                    <input type="text" name="city" id="create-city" value="{{ old('city') }}" placeholder="Abidjan" required
                        list="capitals-list"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <datalist id="capitals-list">
                        @foreach(\App\Helpers\WestAfrica::countries() as $name => $capital)
                        <option value="{{ $capital }}">{{ $name }}</option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Quartier / District</label>
                    <input type="text" name="district" value="{{ old('district') }}" placeholder="Cocody, Plateau..."
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Adresse complète</label>
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="Rue, numéro, immeuble..."
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">L'adresse exacte ne sera visible qu'après réservation confirmée</p>
                </div>
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-semibold text-gray-700">Position GPS (pour la carte)</label>
                        <button type="button" id="geocode-btn"
                            onclick="geocodeAddress()"
                            class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold px-3 py-1.5 rounded-lg transition-colors">
                            📍 Géolocaliser automatiquement
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="number" step="any" name="latitude" id="field-lat" value="{{ old('latitude') }}" placeholder="Latitude ex: 5.3497"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="number" step="any" name="longitude" id="field-lng" value="{{ old('longitude') }}" placeholder="Longitude ex: -4.0167"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <p id="geocode-status" class="text-xs text-gray-400 mt-1">Cliquez sur "Géolocaliser" pour remplir automatiquement depuis l'adresse saisie.</p>
                </div>
            </div>
        </div>

        <!-- STEP 3: Details -->
        <div x-show="step === 3" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Caractéristiques</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach([
                    ['name' => 'capacity', 'label' => 'Capacité (personnes)', 'type' => 'number', 'min' => 1, 'placeholder' => '2'],
                    ['name' => 'bedrooms', 'label' => 'Chambres', 'type' => 'number', 'min' => 0, 'placeholder' => '1'],
                    ['name' => 'bathrooms', 'label' => 'Salles de bain', 'type' => 'number', 'min' => 1, 'placeholder' => '1'],
                    ['name' => 'area', 'label' => 'Superficie (m²)', 'type' => 'number', 'min' => 1, 'placeholder' => '45'],
                    ['name' => 'check_in_time', 'label' => "Heure d'arrivée", 'type' => 'time', 'min' => null, 'placeholder' => ''],
                    ['name' => 'check_out_time', 'label' => 'Heure de départ', 'type' => 'time', 'min' => null, 'placeholder' => ''],
                ] as $field)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $field['label'] }}</label>
                    <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}"
                        {{ $field['min'] !== null ? 'min="'.$field['min'].'"' : '' }}
                        placeholder="{{ $field['placeholder'] }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                @endforeach
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Type de réservation</label>
                    <select name="booking_type" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="request" {{ old('booking_type') === 'request' ? 'selected' : '' }}>Sur demande</option>
                        <option value="instant" {{ old('booking_type') === 'instant' ? 'selected' : '' }}>Instantanée</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Politique d'annulation</label>
                    <select name="cancellation_policy" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="flexible" {{ old('cancellation_policy') === 'flexible' ? 'selected' : '' }}>Flexible (24h)</option>
                        <option value="moderate" {{ old('cancellation_policy') === 'moderate' ? 'selected' : '' }}>Modérée (5 jours)</option>
                        <option value="strict" {{ old('cancellation_policy') === 'strict' ? 'selected' : '' }}>Stricte</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- STEP 4: Pricing -->
        <div x-show="step === 4" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Tarification (FCFA)</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach([
                    ['name' => 'price_per_night', 'label' => 'Prix par nuit', 'required' => true, 'hint' => 'Tarif de base obligatoire'],
                    ['name' => 'price_per_week', 'label' => 'Prix par semaine', 'required' => false, 'hint' => 'Optionnel — avec réduction'],
                    ['name' => 'price_per_month', 'label' => 'Prix par mois', 'required' => false, 'hint' => 'Optionnel — long séjour'],
                    ['name' => 'deposit_amount', 'label' => 'Caution', 'required' => false, 'hint' => 'Remboursée à la fin du séjour'],
                ] as $field)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        {{ $field['label'] }} {!! $field['required'] ? '<span class="text-red-500">*</span>' : '' !!}
                    </label>
                    <div class="relative">
                        <input type="number" name="{{ $field['name'] }}" value="{{ old($field['name']) }}"
                            min="0" step="500" placeholder="0"
                            {{ $field['required'] ? 'required' : '' }}
                            class="w-full pl-4 pr-16 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has($field['name']) ? 'border-red-300' : '' }}">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">FCFA</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $field['hint'] }}</p>
                    @error($field['name'])<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <p class="text-sm text-blue-800">
                    <strong>Note :</strong> Kolo Immo prélève {{ config('kolo.service_fee_percent') }}% de frais de service sur chaque réservation, payés par le locataire. Vous recevez le montant total indiqué.
                </p>
            </div>
        </div>

        <!-- STEP 5: Amenities -->
        <div x-show="step === 5" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Équipements disponibles</h2>
            <p class="text-gray-500 text-sm mb-5">Sélectionnez tout ce qui est disponible dans votre logement</p>
            @php
            $amenityGroups = [
                'Internet & Tech' => ['wifi' => 'Wi-Fi', 'tv' => 'Télévision', 'cable_tv' => 'TV câble/satellite'],
                'Confort' => ['air_conditioning' => 'Climatisation', 'fan' => 'Ventilateur', 'heating' => 'Chauffage'],
                'Cuisine' => ['equipped_kitchen' => 'Cuisine équipée', 'refrigerator' => 'Réfrigérateur', 'oven' => 'Four/Micro-ondes', 'coffee_machine' => 'Machine à café'],
                'Sécurité' => ['security' => 'Sécurité 24h', 'cctv' => 'Caméras de sécurité', 'intercom' => 'Interphone', 'safe' => 'Coffre-fort'],
                'Extérieur' => ['pool' => 'Piscine', 'parking' => 'Parking', 'balcony' => 'Balcon/Terrasse', 'garden' => 'Jardin'],
                'Services' => ['elevator' => 'Ascenseur', 'washer' => 'Machine à laver', 'generator' => 'Groupe électrogène', 'hot_water' => 'Eau chaude'],
            ];
            @endphp
            @foreach($amenityGroups as $group => $items)
            <div class="mb-5">
                <h3 class="text-sm font-bold text-gray-600 mb-3 uppercase tracking-wide">{{ $group }}</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($items as $val => $label)
                    <label class="cursor-pointer flex items-center gap-2 p-3 border-2 rounded-xl transition-all hover:border-blue-200"
                        :class="amenities.includes('{{ $val }}') ? 'border-blue-700 bg-blue-50' : 'border-gray-200'">
                        <input type="checkbox" name="amenities[]" value="{{ $val }}"
                            @click="toggleAmenity('{{ $val }}')"
                            {{ in_array($val, old('amenities', [])) ? 'checked' : '' }}
                            class="hidden">
                        <div class="w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all"
                            :class="amenities.includes('{{ $val }}') ? 'bg-blue-700 border-blue-700' : 'border-gray-300'">
                            <svg x-show="amenities.includes('{{ $val }}')" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium" :class="amenities.includes('{{ $val }}') ? 'text-blue-700' : 'text-gray-700'">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <!-- STEP 6: Rules -->
        <div x-show="step === 6" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Règles de la maison</h2>
            <div class="space-y-4" x-data="{ pets: '{{ old('pets_allowed', '0') }}', smoking: '{{ old('smoking_allowed', '0') }}', parties: '{{ old('parties_allowed', '0') }}' }">
                @foreach([
                    ['name' => 'pets_allowed', 'label' => 'Animaux de compagnie', 'model' => 'pets', 'icon' => '🐾', 'yes' => 'Autorisés', 'no' => 'Non autorisés'],
                    ['name' => 'smoking_allowed', 'label' => 'Tabac/Cigarettes', 'model' => 'smoking', 'icon' => '🚬', 'yes' => 'Autorisé', 'no' => 'Interdit'],
                    ['name' => 'parties_allowed', 'label' => 'Fêtes et événements', 'model' => 'parties', 'icon' => '🎉', 'yes' => 'Autorisées', 'no' => 'Non autorisées'],
                ] as $rule)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $rule['icon'] }}</span>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $rule['label'] }}</p>
                            <p class="text-xs text-gray-500" x-text="{{ $rule['model'] }} === '1' ? '{{ $rule['yes'] }}' : '{{ $rule['no'] }}'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="{{ $rule['name'] }}" :value="{{ $rule['model'] }}">
                        <button type="button"
                            @click="{{ $rule['model'] }} = '0'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border-2 transition-all"
                            :class="{{ $rule['model'] }} === '0' ? 'border-red-500 bg-red-500 text-white' : 'border-gray-200 text-gray-600 hover:border-red-300'">
                            Non
                        </button>
                        <button type="button"
                            @click="{{ $rule['model'] }} = '1'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border-2 transition-all"
                            :class="{{ $rule['model'] }} === '1' ? 'border-green-500 bg-green-500 text-white' : 'border-gray-200 text-gray-600 hover:border-green-300'">
                            Oui
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Règles supplémentaires (optionnel)</label>
                <textarea name="additional_rules" rows="3" placeholder="Ex: Pas de bruit après 22h, chaussures à l'entrée..."
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('additional_rules') }}</textarea>
            </div>
        </div>

        <!-- STEP 7: Photos -->
        <div x-show="step === 7" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Photos de votre logement</h2>
            <p class="text-gray-500 text-sm mb-5">Les annonces avec au moins 5 photos reçoivent 3× plus de réservations</p>

            <div class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center hover:border-blue-400 transition-colors cursor-pointer"
                @click="$refs.photoInput.click()"
                @dragover.prevent
                @drop.prevent="handlePhotos({ target: { files: $event.dataTransfer.files } })">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="font-semibold text-gray-700 mb-1">Cliquez ou glissez vos photos ici</p>
                <p class="text-gray-400 text-xs">JPG, PNG, WebP — Max 5MB par photo — Min 5 photos recommandées</p>
                <input type="file" name="photos[]" multiple accept="image/*" x-ref="photoInput"
                    @change="handlePhotos($event)" class="hidden">
            </div>

            <!-- Preview grid -->
            <div x-show="previewPhotos.length > 0" class="mt-4 grid grid-cols-3 sm:grid-cols-4 gap-2">
                <template x-for="(src, i) in previewPhotos" :key="i">
                    <div class="relative group">
                        <img :src="src" class="w-full h-24 object-cover rounded-xl">
                        <div x-show="i === 0" class="absolute top-1 left-1 bg-blue-700 text-white text-xs px-2 py-0.5 rounded font-semibold">Principale</div>
                        <button type="button" @click.stop="removePhoto(i)"
                            class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs font-bold flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600">
                            ×
                        </button>
                    </div>
                </template>
            </div>
            <p x-show="previewPhotos.length > 0" class="text-xs text-gray-500 mt-2 text-center">
                <span x-text="previewPhotos.length"></span> photo(s) — Cliquez à nouveau sur la zone pour en ajouter d'autres. La 1ère sera la photo principale.
            </p>

            @error('photos')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            @error('photos.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Navigation buttons -->
        <div class="flex items-center justify-between mt-6">
            <button type="button" @click="prevStep()" x-show="step > 1"
                class="flex items-center gap-2 border border-gray-200 text-gray-700 font-semibold px-5 py-3 rounded-xl hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Précédent
            </button>
            <div x-show="step === 1"></div>

            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-400">Étape <span x-text="step"></span> / {{ 7 }}</span>
                <button type="button" @click="nextStep()" x-show="step < totalSteps"
                    class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-3 rounded-xl transition-colors">
                    Suivant
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <button type="submit" x-show="step === totalSteps"
                    class="flex items-center gap-2 bg-orange-400 hover:bg-orange-500 text-white font-bold px-6 py-3 rounded-xl transition-colors shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Publier mon annonce
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
async function geocodeAddress() {
    const city    = document.getElementById('create-city')?.value || '';
    const address = document.querySelector('[name="address"]')?.value || '';
    const query   = [address, city, 'Afrique de l\'Ouest'].filter(Boolean).join(', ');

    const btn    = document.getElementById('geocode-btn');
    const status = document.getElementById('geocode-status');
    btn.textContent = '⏳ Recherche...';
    btn.disabled = true;

    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1`, {
            headers: { 'Accept-Language': 'fr', 'User-Agent': 'KoloImmo/1.0' }
        });
        const data = await res.json();
        if (data.length > 0) {
            document.getElementById('field-lat').value = parseFloat(data[0].lat).toFixed(6);
            document.getElementById('field-lng').value = parseFloat(data[0].lon).toFixed(6);
            status.textContent = '✅ Position trouvée : ' + data[0].display_name.substring(0, 60) + '...';
            status.className = 'text-xs text-green-600 mt-1';
        } else {
            status.textContent = '⚠️ Adresse introuvable. Entrez les coordonnées manuellement.';
            status.className = 'text-xs text-amber-600 mt-1';
        }
    } catch {
        status.textContent = '❌ Erreur de géocodage. Vérifiez votre connexion.';
        status.className = 'text-xs text-red-500 mt-1';
    } finally {
        btn.textContent = '📍 Géolocaliser automatiquement';
        btn.disabled = false;
    }
}
</script>
@endpush
