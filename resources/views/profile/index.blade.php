@extends('layouts.app')

@section('title', 'Mon Profil - Kolo Immo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{ activeTab: '{{ session('tab', 'infos') }}' }">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Mon Profil</h1>
        <p class="text-gray-500 mt-1">Gérez vos informations personnelles et la sécurité de votre compte</p>
    </div>

    <!-- Avatar + info rapide -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <!-- Avatar -->
            <div class="relative flex-shrink-0">
                <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-primary-100">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-primary-700 flex items-center justify-center text-white text-3xl font-bold">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                        </div>
                    @endif
                </div>
                <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                    @csrf
                    <label for="avatarInput"
                        class="absolute -bottom-1 -right-1 w-8 h-8 bg-accent-500 hover:bg-accent-600 text-white rounded-full flex items-center justify-center cursor-pointer shadow-md transition-colors"
                        title="Changer la photo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                    <input type="file" id="avatarInput" name="avatar" accept="image/*" class="hidden"
                        onchange="document.getElementById('avatarForm').submit()">
                </form>
                @error('avatar')
                    <p class="text-red-500 text-xs mt-2 text-center">{{ $message }}</p>
                @enderror
            </div>

            <!-- Infos rapides -->
            <div class="text-center sm:text-left">
                <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-gray-500 text-sm mt-0.5">{{ $user->email }}</p>
                <div class="flex flex-wrap justify-center sm:justify-start gap-2 mt-3">
                    <!-- Rôle -->
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full
                        {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : ($user->role === 'owner' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                        @if($user->role === 'admin') Administrateur
                        @elseif($user->role === 'owner') Propriétaire
                        @elseif($user->role === 'both') Propriétaire & Locataire
                        @else Locataire
                        @endif
                    </span>
                    <!-- KYC -->
                    @if($user->kyc_status === 'verified')
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Identité vérifiée
                    </span>
                    @elseif($kycStatus === 'pending')
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700">
                        En vérification
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-orange-100 text-orange-700">
                        Non vérifié
                    </span>
                    @endif
                    <!-- Badge Locataire de confiance -->
                    @if($user->trust_score >= 80)
                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Locataire de confiance
                    </span>
                    @elseif($user->trust_score >= 60)
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-blue-100 text-blue-700">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Bon locataire
                    </span>
                    @endif
                    <!-- Ville -->
                    @if($user->city)
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $user->city }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="border-b border-gray-100">
            <nav class="flex">
                <button @click="activeTab = 'infos'"
                    :class="activeTab === 'infos' ? 'border-primary-700 text-primary-700 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-semibold border-b-2 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Informations
                </button>
                <button @click="activeTab = 'securite'"
                    :class="activeTab === 'securite' ? 'border-primary-700 text-primary-700 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-semibold border-b-2 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Sécurité
                </button>
                <button @click="activeTab = 'identite'"
                    :class="activeTab === 'identite' ? 'border-primary-700 text-primary-700 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-semibold border-b-2 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Identité (KYC)
                </button>
                <button @click="activeTab = 'reputation'"
                    :class="activeTab === 'reputation' ? 'border-primary-700 text-primary-700 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-semibold border-b-2 transition-all duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Réputation
                </button>
            </nav>
        </div>

        <!-- ────────────────────── TAB: INFORMATIONS ────────────────────── -->
        <div x-show="activeTab === 'infos'" x-cloak class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Informations personnelles</h3>

            @if(session('success') && session('tab') === 'infos')
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            @if($errors->hasAny(['name','email','phone','city','country']))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->only(['name','email','phone','city','country']) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Nom complet -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom complet <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="input-field @error('name') border-red-400 @enderror"
                            placeholder="Votre nom complet">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Téléphone <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                            class="input-field @error('phone') border-red-400 @enderror"
                            placeholder="+225 07 00 00 00 00">
                        @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Adresse email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="input-field @error('email') border-red-400 @enderror"
                        placeholder="exemple@email.com">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Ville -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ville</label>
                        <input type="text" name="city" value="{{ old('city', $user->city) }}"
                            class="input-field"
                            list="profile-capitals-list"
                            placeholder="Ex: Abidjan, Dakar, Bamako...">
                        <datalist id="profile-capitals-list">
                            @foreach(\App\Helpers\WestAfrica::countries() as $countryName => $capital)
                            <option value="{{ $capital }}">
                            @endforeach
                        </datalist>
                    </div>

                    <!-- Pays -->
                    @php $currentCountry = \App\Helpers\WestAfrica::resolve(old('country', $user->country ?? '')); @endphp
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pays</label>
                        <select name="country" class="input-field">
                            <option value="">Sélectionner...</option>
                            @foreach(\App\Helpers\WestAfrica::countries() as $countryName => $capital)
                            <option value="{{ $countryName }}" {{ $currentCountry === $countryName ? 'selected' : '' }}>
                                {{ $countryName }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

        <!-- ────────────────────── TAB: SÉCURITÉ ────────────────────── -->
        <div x-show="activeTab === 'securite'" x-cloak class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Modifier le mot de passe</h3>

            @if(session('success') && session('tab') === 'securite')
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            @if($errors->hasAny(['current_password','password']))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->only(['current_password','password']) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-5 max-w-md">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mot de passe actuel <span class="text-red-500">*</span></label>
                    <input type="password" name="current_password" required
                        class="input-field @error('current_password') border-red-400 @enderror"
                        placeholder="Votre mot de passe actuel">
                    @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nouveau mot de passe <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required
                        class="input-field @error('password') border-red-400 @enderror"
                        placeholder="Minimum 8 caractères, lettres et chiffres">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmer le nouveau mot de passe <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                        class="input-field"
                        placeholder="Répétez le nouveau mot de passe">
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary">
                        Changer le mot de passe
                    </button>
                </div>
            </form>

            <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700">
                <p class="font-semibold mb-1">Conseils de sécurité :</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    <li>Utilisez au moins 8 caractères avec des lettres et des chiffres</li>
                    <li>Évitez les informations personnelles (nom, date de naissance)</li>
                    <li>Ne réutilisez pas un mot de passe d'un autre site</li>
                </ul>
            </div>
        </div>

        <!-- ────────────────────── TAB: IDENTITÉ KYC ────────────────────── -->
        <div x-show="activeTab === 'identite'" x-cloak class="p-6">
            @php
                $steps = [
                    ['label' => 'Soumettre',       'desc' => 'Téléchargez vos documents'],
                    ['label' => 'En vérification', 'desc' => 'Notre équipe examine vos documents'],
                    ['label' => 'Vérifié',          'desc' => 'Votre identité est confirmée'],
                ];
                $currentStep = match($kycStatus) { 'none' => 0, 'pending' => 1, 'approved' => 2, 'rejected' => 0, default => 0 };
            @endphp

            <!-- Statut KYC -->
            @if($kycStatus === 'approved')
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl mb-6">
                <svg class="w-8 h-8 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-bold text-green-800">Identité vérifiée !</p>
                    <p class="text-green-700 text-sm">Votre compte est pleinement vérifié. Vous avez accès à toutes les fonctionnalités.</p>
                </div>
            </div>
            @elseif($kycStatus === 'pending')
            <div class="flex items-center gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-xl mb-6">
                <svg class="w-8 h-8 text-yellow-500 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-bold text-yellow-800">Vérification en cours</p>
                    <p class="text-yellow-700 text-sm">Nos équipes examinent vos documents. Délai : 24-48h ouvrées.</p>
                </div>
            </div>
            @elseif($kycStatus === 'rejected')
            <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl mb-6">
                <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-bold text-red-800">Documents rejetés</p>
                    <p class="text-red-700 text-sm">Motif : {{ $user->kycDocuments->last()?->rejection_reason }}. Veuillez resoumettre des documents valides.</p>
                </div>
            </div>
            @endif

            <!-- Barre de progression -->
            <div class="flex items-center justify-between mb-8">
                @foreach($steps as $i => $s)
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                        {{ $i < $currentStep ? 'bg-green-500' : ($i === $currentStep ? 'bg-primary-700' : 'bg-gray-100') }}">
                        @if($i < $currentStep)
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        @else
                        <span class="text-sm font-bold {{ $i === $currentStep ? 'text-white' : 'text-gray-400' }}">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-center {{ $i === $currentStep ? 'text-primary-700' : ($i < $currentStep ? 'text-green-600' : 'text-gray-400') }}">{{ $s['label'] }}</p>
                    <p class="text-xs text-gray-400 text-center hidden sm:block">{{ $s['desc'] }}</p>
                </div>
                @if($i < count($steps) - 1)
                <div class="flex-1 h-0.5 mb-8 {{ $i < $currentStep ? 'bg-green-300' : 'bg-gray-200' }}"></div>
                @endif
                @endforeach
            </div>

            @if($kycStatus === 'none' || $kycStatus === 'rejected')
            <!-- Formulaire de soumission KYC -->
            <div x-data="{
                docPreview: null,
                selfiePreview: null,
                titlePreview: null,
                previewFile(input, target) {
                    const file = input.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (e) => { this[target] = e.target.result; };
                    reader.readAsDataURL(file);
                }
            }">
                <h4 class="font-bold text-gray-900 text-base mb-5">Soumettre vos documents</h4>

                @if(session('success') && session('tab') === 'identite')
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->hasAny(['type','document_file','selfie_file']))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->only(['type','document_file','selfie_file']) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('profile.kyc.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <!-- Type de document -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Type de document d'identité <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach(['cni' => "Carte d'identité", 'passport' => 'Passeport', 'residence_permit' => 'Carte de séjour'] as $val => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="{{ $val }}" class="sr-only peer"
                                    {{ old('type') === $val ? 'checked' : '' }} required>
                                <div class="p-3 border-2 border-gray-200 rounded-xl text-center transition-all peer-checked:border-primary-700 peer-checked:bg-primary-50 hover:border-primary-200 cursor-pointer">
                                    <p class="font-semibold text-xs text-gray-700">{{ $label }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Photo du document -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Photo du document <span class="text-red-500">*</span></label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-primary-400 transition-colors cursor-pointer"
                            @click="$refs.docInput.click()">
                            <div x-show="!docPreview" class="py-3">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Cliquez pour uploader le recto/verso de votre document</p>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — Max 5 Mo</p>
                            </div>
                            <img x-show="docPreview" x-cloak :src="docPreview" class="max-h-40 mx-auto rounded-lg object-contain">
                            <input type="file" name="document_file" accept="image/*,application/pdf" x-ref="docInput"
                                @change="previewFile($event.target, 'docPreview')" class="hidden" required>
                        </div>
                        @error('document_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Selfie -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Selfie avec le document <span class="text-red-500">*</span></label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-primary-400 transition-colors cursor-pointer"
                            @click="$refs.selfieInput.click()">
                            <div x-show="!selfiePreview" class="py-3">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Photo de vous tenant votre document bien visible</p>
                                <p class="text-xs text-gray-400 mt-1">Assurez-vous que votre visage et le document sont nets</p>
                            </div>
                            <img x-show="selfiePreview" x-cloak :src="selfiePreview" class="max-h-40 mx-auto rounded-lg object-contain">
                            <input type="file" name="selfie_file" accept="image/*" x-ref="selfieInput"
                                @change="previewFile($event.target, 'selfiePreview')" class="hidden" required>
                        </div>
                        @error('selfie_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    @if($user->role === 'owner' || $user->role === 'both')
                    <!-- Titre de propriété (propriétaires) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Titre de propriété ou bail
                            <span class="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded ml-2">Propriétaire</span>
                        </label>
                        <div class="border-2 border-dashed border-orange-200 rounded-xl p-4 text-center hover:border-orange-400 transition-colors cursor-pointer"
                            @click="$refs.titleInput.click()">
                            <div x-show="!titlePreview" class="py-3">
                                <svg class="w-8 h-8 text-orange-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Titre de propriété ou contrat de bail (optionnel)</p>
                            </div>
                            <img x-show="titlePreview" x-cloak :src="titlePreview" class="max-h-32 mx-auto rounded-lg object-contain">
                            <input type="file" name="title_deed" accept="image/*,application/pdf" x-ref="titleInput"
                                @change="previewFile($event.target, 'titlePreview')" class="hidden">
                        </div>
                    </div>
                    @endif

                    <button type="submit" class="btn-primary w-full py-3.5">
                        Soumettre pour vérification
                    </button>
                </form>
            </div>
            @endif

            <!-- Avantages de la vérification -->
            <div class="mt-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-5 border border-blue-200">
                <h4 class="font-bold text-blue-900 text-sm mb-3">Pourquoi vérifier votre identité ?</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach([
                        ['title' => 'Sécurité renforcée', 'desc' => 'Protège la communauté contre les fraudes'],
                        ['title' => 'Badge Vérifié', 'desc' => 'Votre profil affiche un badge de confiance'],
                        ['title' => 'Plus de réservations', 'desc' => 'Les annonces vérifiées reçoivent 5× plus de demandes'],
                        ['title' => 'Paiements débloqués', 'desc' => 'Accès complet aux paiements Mobile Money'],
                    ] as $b)
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-blue-900 text-xs">{{ $b['title'] }}</p>
                            <p class="text-blue-700 text-xs">{{ $b['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ────────────────────── TAB: RÉPUTATION ────────────────────── -->
        <div x-show="activeTab === 'reputation'" x-cloak class="p-6">
            @php
                $score      = (float) ($user->trust_score ?? 0);
                $scoreColor = $score >= 80 ? 'emerald' : ($score >= 60 ? 'blue' : ($score >= 40 ? 'yellow' : 'red'));
                $scoreLabel = $score >= 80 ? 'Locataire de confiance' : ($score >= 60 ? 'Bon locataire' : ($score >= 40 ? 'En progression' : 'Score à améliorer'));

                $tenantReviews = $user->reviewsReceived()
                    ->where('type', 'owner_to_tenant')
                    ->where('is_flagged', false)
                    ->with('reviewer:id,name')
                    ->latest()
                    ->get();
            @endphp

            <h3 class="text-lg font-bold text-gray-900 mb-6">Score de réputation</h3>

            <!-- Score principal -->
            <div class="bg-{{ $scoreColor }}-50 border border-{{ $scoreColor }}-100 rounded-2xl p-6 mb-6">
                <div class="flex items-center gap-6">
                    <!-- Cercle score -->
                    <div class="relative w-24 h-24 flex-shrink-0">
                        <svg class="w-24 h-24 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#e5e7eb" stroke-width="2.5"/>
                            <circle cx="18" cy="18" r="15.9155" fill="none"
                                stroke="{{ ['emerald' => '#10b981', 'blue' => '#3b82f6', 'yellow' => '#f59e0b', 'red' => '#ef4444'][$scoreColor] }}"
                                stroke-width="2.5"
                                stroke-dasharray="{{ round($score, 1) }} {{ 100 - round($score, 1) }}"
                                stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xl font-bold text-{{ $scoreColor }}-700">{{ round($score) }}</span>
                            <span class="text-xs text-{{ $scoreColor }}-500">/100</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-{{ $scoreColor }}-800">{{ $scoreLabel }}</p>
                        <p class="text-sm text-{{ $scoreColor }}-600 mt-1">
                            Basé sur {{ $tenantReviews->count() }} évaluation(s) de propriétaires
                        </p>
                        <div class="w-full bg-{{ $scoreColor }}-200 rounded-full h-2 mt-3 max-w-xs">
                            <div class="bg-{{ $scoreColor }}-500 h-2 rounded-full transition-all duration-500"
                                style="width: {{ $score }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grille des moyennes par critère -->
            @if($tenantReviews->count() > 0)
            @php
                $avgOverall       = $tenantReviews->avg('rating_overall');
                $avgCleanliness   = $tenantReviews->avg('rating_cleanliness');
                $avgCommunication = $tenantReviews->avg('rating_communication');
                $avgPayment       = $tenantReviews->avg('rating_payment');
                $subCriteria = array_filter([
                    'Comportement général' => $avgOverall,
                    'Soin du logement'     => $avgCleanliness,
                    'Communication'        => $avgCommunication,
                    'Ponctualité paiement' => $avgPayment,
                ]);
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                @foreach($subCriteria as $label => $avg)
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-700">{{ $label }}</span>
                        <span class="text-sm font-bold text-gray-900">{{ number_format($avg, 1) }}<span class="text-gray-400 font-normal">/5</span></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ ($avg / 5) * 100 }}%"></div>
                    </div>
                    <div class="flex justify-end gap-0.5 mt-1.5">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-3 h-3 {{ $i <= round($avg) ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Historique des évaluations reçues -->
            <h4 class="font-bold text-gray-800 mb-4">Évaluations reçues de propriétaires</h4>
            <div class="space-y-4">
                @foreach($tenantReviews as $review)
                <div class="bg-white border border-gray-100 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm">
                                {{ strtoupper(substr($review->reviewer->name ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $review->reviewer->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $review->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 bg-amber-50 px-2.5 py-1 rounded-full">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-xs font-bold text-amber-700">{{ number_format($review->rating_overall, 1) }}</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $review->comment }}</p>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-10 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <p class="text-sm font-medium">Aucune évaluation pour l'instant</p>
                <p class="text-xs mt-1">Votre score de confiance sera calculé après votre premier séjour.</p>
            </div>
            @endif

            <!-- Explication du score -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-100 rounded-xl">
                <p class="text-sm font-semibold text-blue-800 mb-2">Comment est calculé votre score ?</p>
                <ul class="space-y-1 text-xs text-blue-700">
                    <li>· Moyenne de toutes les notes globales reçues des propriétaires (échelle 1→5)</li>
                    <li>· Convertie sur 100 : (moyenne / 5) × 100</li>
                    <li>· Score minimum : 20/100 · Score maximum : 100/100</li>
                    <li>· Un score ≥ 80 vous donne le badge <strong>Locataire de confiance</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
