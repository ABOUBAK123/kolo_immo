@extends('layouts.app')

@section('title', 'Modifier : ' . $property->title . ' - Kolo Immo')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{
        step: 1,
        totalSteps: 7,
        amenities: {{ json_encode(old('amenities', $property->amenities->pluck('amenity')->toArray())) }},
        type: '{{ old('type', $property->type) }}',
        previewPhotos: [],
        setType(t) { this.type = t; },
        toggleAmenity(a) {
            if(this.amenities.includes(a)) {
                this.amenities = this.amenities.filter(x => x !== a);
            } else {
                this.amenities.push(a);
            }
        },
        handlePhotos(event) {
            this.previewPhotos = [];
            Array.from(event.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => { this.previewPhotos.push(e.target.result); };
                reader.readAsDataURL(file);
            });
        }
    }">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Modifier l'annonce</h1>
            <p class="text-gray-500 text-sm">{{ $property->title }}</p>
        </div>
        <a href="{{ route('properties.show', $property) }}"
            class="border border-gray-200 text-gray-600 font-semibold px-4 py-2 rounded-xl text-sm hover:bg-gray-50 transition-colors">
            Voir l'annonce
        </a>
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
                        <template x-if="{{ $i + 1 }} >= step"><span>{{ $i + 1 }}</span></template>
                    </div>
                    <span class="text-xs font-medium hidden sm:block" :class="{{ $i + 1 }} === step ? 'text-blue-700' : 'text-gray-400'">{{ $label }}</span>
                </div>
                @if($i < 6)<div class="w-6 h-0.5 flex-shrink-0" :class="{{ $i + 1 }} < step ? 'bg-green-300' : 'bg-gray-200'"></div>@endif
            </div>
            @endforeach
        </div>
        <div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-blue-700 rounded-full transition-all duration-300" :style="'width: ' + ((step - 1) / (totalSteps - 1) * 100) + '%'"></div>
        </div>
    </div>

    <form action="{{ route('owner.properties.update', $property) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- STEP 1: Type & Basic Info -->
        <div x-show="step === 1" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Type et présentation</h2>
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Type de logement</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach(['studio' => 'Studio', 'appartement' => 'Appartement', 'villa' => 'Villa', 'chambre' => 'Chambre', 'duplex' => 'Duplex', 'maison' => 'Maison'] as $val => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $val }}" class="sr-only peer"
                            @change="setType('{{ $val }}')"
                            {{ old('type', $property->type) === $val ? 'checked' : '' }}>
                        <div class="p-3 border-2 border-gray-200 rounded-xl text-center transition-all peer-checked:border-blue-700 peer-checked:bg-blue-50 hover:border-blue-200"
                            :class="type === '{{ $val }}' ? 'border-blue-700 bg-blue-50' : ''">
                            <p class="font-semibold text-sm" :class="type === '{{ $val }}' ? 'text-blue-700' : 'text-gray-700'">{{ $label }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Titre de l'annonce</label>
                <input type="text" name="title" value="{{ old('title', $property->title) }}"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="5"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" required>{{ old('description', $property->description) }}</textarea>
            </div>
        </div>

        <!-- STEP 2: Location -->
        <div x-show="step === 2" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Localisation</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pays</label>
                    <select name="country" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['CI' => "Côte d'Ivoire", 'SN' => 'Sénégal', 'BF' => 'Burkina Faso', 'ML' => 'Mali', 'TG' => 'Togo', 'BJ' => 'Bénin'] as $code => $name)
                        <option value="{{ $code }}" {{ old('country', $property->country) === $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $property->city) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Quartier</label>
                    <input type="text" name="district" value="{{ old('district', $property->district) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $property->address) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- STEP 3: Details -->
        <div x-show="step === 3" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Caractéristiques</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach([
                    ['name' => 'capacity', 'label' => 'Capacité'],
                    ['name' => 'bedrooms', 'label' => 'Chambres'],
                    ['name' => 'bathrooms', 'label' => 'Salles de bain'],
                    ['name' => 'area', 'label' => 'Superficie (m²)'],
                ] as $field)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $field['label'] }}</label>
                    <input type="number" name="{{ $field['name'] }}" min="0"
                        value="{{ old($field['name'], $property->{$field['name']}) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                @endforeach
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Type de réservation</label>
                    <select name="booking_type" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="request" {{ old('booking_type', $property->booking_type) === 'request' ? 'selected' : '' }}>Sur demande</option>
                        <option value="instant" {{ old('booking_type', $property->booking_type) === 'instant' ? 'selected' : '' }}>Instantanée</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Annulation</label>
                    <select name="cancellation_policy" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="flexible" {{ old('cancellation_policy', $property->cancellation_policy) === 'flexible' ? 'selected' : '' }}>Flexible</option>
                        <option value="moderate" {{ old('cancellation_policy', $property->cancellation_policy) === 'moderate' ? 'selected' : '' }}>Modérée</option>
                        <option value="strict" {{ old('cancellation_policy', $property->cancellation_policy) === 'strict' ? 'selected' : '' }}>Stricte</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- STEP 4: Pricing -->
        <div x-show="step === 4" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Tarification</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach(['price_per_night' => 'Prix par nuit *', 'price_per_week' => 'Prix par semaine', 'price_per_month' => 'Prix par mois', 'deposit_amount' => 'Caution'] as $field => $label)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $label }}</label>
                    <div class="relative">
                        <input type="number" name="{{ $field }}" min="0" step="500"
                            value="{{ old($field, $property->{$field}) }}"
                            class="w-full pl-4 pr-16 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            {{ $field === 'price_per_night' ? 'required' : '' }}>
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">FCFA</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- STEP 5: Amenities -->
        <div x-show="step === 5" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Équipements</h2>
            @php
            $allAmenities = [
                'wifi' => 'Wi-Fi', 'climatisation' => 'Climatisation', 'parking' => 'Parking',
                'piscine' => 'Piscine', 'cuisine' => 'Cuisine équipée', 'television' => 'Télévision',
                'machine_a_laver' => 'Machine à laver', 'gardien' => 'Gardien 24h/24',
                'ascenseur' => 'Ascenseur', 'generateur' => 'Groupe électrogène',
                'eau_chaude' => 'Eau chaude', 'balcon' => 'Balcon/Terrasse',
                'refrigerateur' => 'Réfrigérateur', 'four' => 'Four/Micro-ondes',
            ];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($allAmenities as $val => $label)
                <label class="cursor-pointer flex items-center gap-2 p-3 border-2 rounded-xl transition-all hover:border-blue-200"
                    :class="amenities.includes('{{ $val }}') ? 'border-blue-700 bg-blue-50' : 'border-gray-200'">
                    <input type="checkbox" name="amenities[]" value="{{ $val }}"
                        @click="toggleAmenity('{{ $val }}')"
                        {{ in_array($val, old('amenities', $property->amenities->pluck('amenity')->toArray())) ? 'checked' : '' }}
                        class="hidden">
                    <div class="w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0"
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

        <!-- STEP 6: Rules -->
        <div x-show="step === 6" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Règles de la maison</h2>
            <div class="space-y-4" x-data="{
                pets: '{{ old('pets_allowed', $property->pets_allowed ? '1' : '0') }}',
                smoking: '{{ old('smoking_allowed', $property->smoking_allowed ? '1' : '0') }}',
                parties: '{{ old('parties_allowed', $property->parties_allowed ? '1' : '0') }}'
            }">
                @foreach([
                    ['name' => 'pets_allowed', 'label' => 'Animaux de compagnie', 'model' => 'pets', 'icon' => '🐾'],
                    ['name' => 'smoking_allowed', 'label' => 'Fumeur', 'model' => 'smoking', 'icon' => '🚬'],
                    ['name' => 'parties_allowed', 'label' => 'Fêtes', 'model' => 'parties', 'icon' => '🎉'],
                ] as $rule)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $rule['icon'] }}</span>
                        <p class="font-semibold text-gray-900 text-sm">{{ $rule['label'] }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="{{ $rule['name'] }}" :value="{{ $rule['model'] }}">
                        <button type="button" @click="{{ $rule['model'] }} = '0'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border-2 transition-all"
                            :class="{{ $rule['model'] }} === '0' ? 'border-red-500 bg-red-500 text-white' : 'border-gray-200 text-gray-600'">Non</button>
                        <button type="button" @click="{{ $rule['model'] }} = '1'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border-2 transition-all"
                            :class="{{ $rule['model'] }} === '1' ? 'border-green-500 bg-green-500 text-white' : 'border-gray-200 text-gray-600'">Oui</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- STEP 7: Photos -->
        <div x-show="step === 7" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Photos</h2>

            <!-- Existing photos -->
            @if($property->photos->isNotEmpty())
            <div class="mb-5">
                <p class="text-sm font-semibold text-gray-700 mb-3">Photos actuelles ({{ $property->photos->count() }})</p>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    @foreach($property->photos as $photo)
                    <div class="relative group">
                        <img src="{{ asset('storage/' . $photo->path) }}" class="w-full h-20 object-cover rounded-lg">
                        <form action="{{ route('owner.properties.photos.delete', [$property, $photo]) }}" method="POST"
                            class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600"
                                onclick="return confirm('Supprimer cette photo ?')">×</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center hover:border-blue-400 transition-colors cursor-pointer" @click="$refs.photoInput.click()">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                </svg>
                <p class="font-semibold text-gray-700 text-sm">Ajouter de nouvelles photos</p>
                <input type="file" name="photos[]" multiple accept="image/*" x-ref="photoInput" @change="handlePhotos($event)" class="hidden">
            </div>

            <div x-show="previewPhotos.length > 0" class="mt-3 grid grid-cols-4 gap-2">
                <template x-for="(src, i) in previewPhotos" :key="i">
                    <img :src="src" class="w-full h-20 object-cover rounded-xl">
                </template>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex items-center justify-between mt-6">
            <button type="button" @click="step--" x-show="step > 1"
                class="flex items-center gap-2 border border-gray-200 text-gray-700 font-semibold px-5 py-3 rounded-xl hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Précédent
            </button>
            <div x-show="step === 1"></div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-400">Étape <span x-text="step"></span> / {{ 7 }}</span>
                <button type="button" @click="step++" x-show="step < totalSteps"
                    class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-3 rounded-xl transition-colors">
                    Suivant <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <button type="submit" x-show="step === totalSteps"
                    class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-xl transition-colors shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
