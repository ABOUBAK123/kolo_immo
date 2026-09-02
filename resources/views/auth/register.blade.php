@extends('layouts.app')

@section('title', 'Inscription - Kolo Immo')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl mx-auto">

        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <div class="w-12 h-12 bg-blue-700 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-blue-700">Kolo <span class="text-orange-400">Immo</span></span>
            </a>
            <h1 class="mt-4 text-2xl font-bold text-gray-900">Créer votre compte</h1>
            <p class="text-gray-500 text-sm mt-1">Rejoignez des milliers d'utilisateurs en Afrique de l'Ouest</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8" x-data="{ role: '{{ old('role', request('role', 'tenant')) }}' }">

            <!-- Errors -->
            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3">
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Role selector -->
                <div class="mb-6">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Je suis :</p>
                    <div class="grid grid-cols-3 gap-3">
                        <!-- Tenant -->
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="tenant" x-model="role" class="sr-only peer">
                            <div class="p-3 border-2 border-gray-200 rounded-xl text-center transition-all
                                peer-checked:border-blue-700 peer-checked:bg-blue-50 hover:border-blue-300">
                                <div class="w-10 h-10 mx-auto mb-2 rounded-xl flex items-center justify-center"
                                    :class="role === 'tenant' ? 'bg-blue-700' : 'bg-gray-100'">
                                    <svg class="w-5 h-5" :class="role === 'tenant' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <p class="font-semibold text-xs" :class="role === 'tenant' ? 'text-blue-700' : 'text-gray-700'">Locataire</p>
                                <p class="text-xs text-gray-400 mt-0.5 hidden sm:block">Je cherche un logement</p>
                            </div>
                        </label>

                        <!-- Owner -->
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="owner" x-model="role" class="sr-only peer">
                            <div class="p-3 border-2 border-gray-200 rounded-xl text-center transition-all
                                peer-checked:border-orange-400 peer-checked:bg-orange-50 hover:border-orange-200">
                                <div class="w-10 h-10 mx-auto mb-2 rounded-xl flex items-center justify-center"
                                    :class="role === 'owner' ? 'bg-orange-400' : 'bg-gray-100'">
                                    <svg class="w-5 h-5" :class="role === 'owner' ? 'text-white' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                    </svg>
                                </div>
                                <p class="font-semibold text-xs" :class="role === 'owner' ? 'text-orange-600' : 'text-gray-700'">Propriétaire</p>
                                <p class="text-xs text-gray-400 mt-0.5 hidden sm:block">Je loue mon bien</p>
                            </div>
                        </label>

                        <!-- Agent -->
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="agent" x-model="role" class="sr-only peer">
                            <div class="p-3 border-2 border-gray-200 rounded-xl text-center transition-all
                                peer-checked:border-purple-600 peer-checked:bg-purple-50 hover:border-purple-200">
                                <div class="w-10 h-10 mx-auto mb-2 rounded-xl flex items-center justify-center"
                                    :class="role === 'agent' ? 'bg-purple-600' : 'bg-gray-100'">
                                    <svg class="w-5 h-5" :class="role === 'agent' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="font-semibold text-xs" :class="role === 'agent' ? 'text-purple-700' : 'text-gray-700'">Agent Immo</p>
                                <p class="text-xs text-gray-400 mt-0.5 hidden sm:block">Commission 3%</p>
                            </div>
                        </label>
                    </div>
                    <!-- Notice pour owner/agent -->
                    <div x-show="role === 'owner' || role === 'agent'" x-cloak
                        class="mt-3 flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-amber-700">Ce type de compte sera activé par l'administrateur après vérification de votre pièce d'identité ci-dessous.</p>
                    </div>
                </div>

                <!-- Code agent (facultatif, pour propriétaires/locataires parrainés par un agent) -->
                <div class="mb-4" x-show="role === 'tenant' || role === 'owner'" x-cloak>
                    <label for="agent_code" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Code agent <span class="text-gray-400 font-normal">(facultatif)</span>
                    </label>
                    <input type="text" id="agent_code" name="agent_code" value="{{ old('agent_code') }}"
                        placeholder="Ex: AGT-K3F9QX"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm uppercase {{ $errors->has('agent_code') ? 'border-red-300 bg-red-50' : '' }}">
                    <p class="text-xs text-gray-400 mt-1">Si un agent immobilier vous a communiqué un code, saisissez-le ici.</p>
                </div>

                <!-- Name fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="prenom" class="block text-sm font-semibold text-gray-700 mb-1.5">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}"
                            placeholder="Aminata"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('prenom') ? 'border-red-300 bg-red-50' : '' }}"
                            required autocomplete="given-name">
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Nom de famille</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            placeholder="Koné"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('name') ? 'border-red-300 bg-red-50' : '' }}"
                            required autocomplete="family-name">
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Adresse email</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="exemple@email.com"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('email') ? 'border-red-300 bg-red-50' : '' }}"
                            autocomplete="email">
                    </div>
                </div>

                <!-- Phone -->
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Numéro de téléphone <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                            placeholder="+225 07 XX XX XX XX"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('phone') ? 'border-red-300 bg-red-50' : '' }}"
                            required autocomplete="tel">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Un code de vérification sera envoyé à ce numéro</p>
                </div>

                <!-- Password -->
                <div class="mb-4" x-data="{ showPwd: false }">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Mot de passe</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input :type="showPwd ? 'text' : 'password'" id="password" name="password"
                            placeholder="Minimum 8 caractères"
                            class="w-full pl-10 pr-12 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('password') ? 'border-red-300 bg-red-50' : '' }}"
                            autocomplete="new-password" required>
                        <button type="button" @click="showPwd = !showPwd" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Password confirm -->
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmer le mot de passe</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            placeholder="Répétez le mot de passe"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            autocomplete="new-password" required>
                    </div>
                </div>

                <!-- Location -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="country" class="block text-sm font-semibold text-gray-700 mb-1.5">Pays</label>
                        <select id="country" name="country"
                            onchange="(function(s){var c=@json(\App\Helpers\WestAfrica::countries());var cap=c[s.value]||'';var ci=document.getElementById('city');ci.placeholder=cap?'Ex: '+cap:'Votre ville';})(this)"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white {{ $errors->has('country') ? 'border-red-300' : '' }}">
                            <option value="">Sélectionner...</option>
                            @foreach(\App\Helpers\WestAfrica::countries() as $countryName => $capital)
                            <option value="{{ $countryName }}" {{ old('country') === $countryName ? 'selected' : '' }}>{{ $countryName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-semibold text-gray-700 mb-1.5">Ville</label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}"
                            placeholder="Abidjan"
                            list="reg-capitals-list"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('city') ? 'border-red-300 bg-red-50' : '' }}">
                        <datalist id="reg-capitals-list">
                            @foreach(\App\Helpers\WestAfrica::countries() as $countryName => $capital)
                            <option value="{{ $capital }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <!-- KYC (vérification d'identité) — obligatoire pour Propriétaire / Agent Immo -->
                <div x-show="role === 'owner' || role === 'agent'" x-cloak
                    class="mb-6 border border-gray-200 rounded-xl p-4 bg-gray-50/60">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <p class="font-semibold text-sm text-gray-800">Vérification d'identité (KYC)</p>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">Requise pour activer votre compte. Vos documents sont examinés par notre équipe.</p>

                    <div class="mb-4">
                        <label for="kyc_type" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Type de pièce <span class="text-red-500">*</span>
                        </label>
                        <select id="kyc_type" name="kyc_type"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white {{ $errors->has('kyc_type') ? 'border-red-300' : '' }}"
                            :required="role === 'owner' || role === 'agent'">
                            <option value="">Sélectionner...</option>
                            <option value="cni" {{ old('kyc_type') === 'cni' ? 'selected' : '' }}>Carte Nationale d'Identité</option>
                            <option value="passport" {{ old('kyc_type') === 'passport' ? 'selected' : '' }}>Passeport</option>
                            <option value="residence_permit" {{ old('kyc_type') === 'residence_permit' ? 'selected' : '' }}>Titre de séjour</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="kyc_document" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Pièce justificative <span class="text-red-500">*</span>
                            </label>
                            <input type="file" id="kyc_document" name="kyc_document" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full text-xs text-gray-600 border border-gray-200 rounded-xl px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer {{ $errors->has('kyc_document') ? 'border-red-300' : '' }}"
                                :required="role === 'owner' || role === 'agent'">
                            <p class="text-xs text-gray-400 mt-1">PDF, JPG ou PNG · max 5 Mo</p>
                        </div>
                        <div>
                            <label for="kyc_selfie" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Selfie <span class="text-gray-400 font-normal">(facultatif)</span>
                            </label>
                            <input type="file" id="kyc_selfie" name="kyc_selfie" accept="image/*"
                                class="w-full text-xs text-gray-600 border border-gray-200 rounded-xl px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer {{ $errors->has('kyc_selfie') ? 'border-red-300' : '' }}">
                            <p class="text-xs text-gray-400 mt-1">JPG ou PNG · max 2 Mo</p>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-start gap-3 mb-6">
                    <input type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}
                        class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500" required>
                    <label for="terms" class="text-sm text-gray-600 cursor-pointer leading-relaxed">
                        J'accepte les
                        <a href="#" class="text-blue-700 font-semibold hover:text-blue-900">Conditions d'utilisation</a>
                        et la
                        <a href="#" class="text-blue-700 font-semibold hover:text-blue-900">Politique de confidentialité</a>
                        de Kolo Immo.
                    </label>
                </div>

                <button type="submit"
                    class="w-full font-bold py-3.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md text-sm text-white"
                    :class="role === 'owner' ? 'bg-orange-400 hover:bg-orange-500' : 'bg-blue-700 hover:bg-blue-800'">
                    Créer mon compte
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Déjà inscrit ?
                <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:text-blue-900 transition-colors">
                    Se connecter
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
