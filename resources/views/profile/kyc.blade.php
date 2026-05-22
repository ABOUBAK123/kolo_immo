@extends('layouts.app')

@section('title', 'Vérification d\'identité KYC - Kolo Immo')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Vérification d'identité</h1>
        <p class="text-gray-500">Vérifiez votre identité pour profiter de toutes les fonctionnalités de Kolo Immo</p>
    </div>

    <!-- Status tracker -->
    @php
    $kycStatus = Auth::user()->kycDocuments->last()?->status ?? 'none';
    $steps = [
        ['id' => 'none', 'label' => 'Soumettre', 'desc' => 'Téléchargez vos documents'],
        ['id' => 'pending', 'label' => 'En vérification', 'desc' => 'Notre équipe examine vos documents'],
        ['id' => 'approved', 'label' => 'Vérifié', 'desc' => 'Votre identité est confirmée'],
    ];
    $currentStep = match($kycStatus) { 'none' => 0, 'pending' => 1, 'approved' => 2, 'rejected' => 0, default => 0 };
    @endphp

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <!-- Status badge -->
        @if($kycStatus === 'approved')
        <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl mb-5">
            <svg class="w-8 h-8 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-bold text-green-800">Identité vérifiée !</p>
                <p class="text-green-700 text-sm">Votre compte est pleinement vérifié. Vous avez accès à toutes les fonctionnalités.</p>
            </div>
        </div>
        @elseif($kycStatus === 'pending')
        <div class="flex items-center gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-xl mb-5">
            <svg class="w-8 h-8 text-yellow-500 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-bold text-yellow-800">Vérification en cours</p>
                <p class="text-yellow-700 text-sm">Nos équipes examinent vos documents. Délai: 24-48h ouvrées.</p>
            </div>
        </div>
        @elseif($kycStatus === 'rejected')
        <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl mb-5">
            <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-bold text-red-800">Documents rejetés</p>
                <p class="text-red-700 text-sm">Motif: {{ Auth::user()->kycDocuments->last()?->rejection_reason }}. Veuillez resoumettre des documents valides.</p>
            </div>
        </div>
        @endif

        <!-- Step tracker -->
        <div class="flex items-center justify-between">
            @foreach($steps as $i => $s)
            <div class="flex-1 flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                    {{ $i < $currentStep ? 'bg-green-500' : ($i === $currentStep ? 'bg-blue-700' : 'bg-gray-100') }}">
                    @if($i < $currentStep)
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @else
                    <span class="text-sm font-bold {{ $i === $currentStep ? 'text-white' : 'text-gray-400' }}">{{ $i + 1 }}</span>
                    @endif
                </div>
                <p class="text-xs font-semibold text-center {{ $i === $currentStep ? 'text-blue-700' : ($i < $currentStep ? 'text-green-600' : 'text-gray-400') }}">{{ $s['label'] }}</p>
                <p class="text-xs text-gray-400 text-center hidden sm:block">{{ $s['desc'] }}</p>
            </div>
            @if($i < count($steps) - 1)
            <div class="flex-1 h-0.5 mb-8 {{ $i < $currentStep ? 'bg-green-300' : 'bg-gray-200' }}"></div>
            @endif
            @endforeach
        </div>
    </div>

    @if($kycStatus === 'none' || $kycStatus === 'rejected')
    <!-- Upload form -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6" x-data="{
        docPreview: null,
        selfiePreview: null,
        titlePreview: null,
        previewFile(input, target) {
            const file = input.files[0];
            if(!file) return;
            const reader = new FileReader();
            reader.onload = (e) => { this[target] = e.target.result; };
            reader.readAsDataURL(file);
        }
    }">
        <h2 class="font-bold text-gray-900 text-lg mb-5">Soumettre vos documents</h2>

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('profile.kyc.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Document type -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Type de document d'identité</label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['cni' => 'Carte Nationale d\'Identité', 'passport' => 'Passeport', 'residence_permit' => 'Carte de séjour'] as $val => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $val }}" class="sr-only peer"
                            {{ old('type') === $val ? 'checked' : '' }} required>
                        <div class="p-3 border-2 border-gray-200 rounded-xl text-center transition-all peer-checked:border-blue-700 peer-checked:bg-blue-50 hover:border-blue-200 cursor-pointer">
                            <p class="font-semibold text-sm text-gray-700 peer-checked:text-blue-700">{{ $label }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Document photo -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Photo du document <span class="text-red-500">*</span></label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition-colors cursor-pointer"
                    @click="$refs.docInput.click()">
                    <div x-show="!docPreview" class="py-3">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        </svg>
                        <p class="text-sm text-gray-500">Cliquez pour uploader le recto/verso de votre document</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG — Max 5MB</p>
                    </div>
                    <img x-show="docPreview" x-cloak :src="docPreview" class="max-h-40 mx-auto rounded-lg object-contain">
                    <input type="file" name="document_file" accept="image/*,application/pdf" x-ref="docInput"
                        @change="previewFile($event.target, 'docPreview')" class="hidden" required>
                </div>
                @error('document_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Selfie -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Selfie avec le document <span class="text-red-500">*</span></label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition-colors cursor-pointer"
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

            <!-- Owner: Title deed -->
            @if(Auth::user()->role === 'owner')
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Titre de propriété ou bail (propriétaires)
                    <span class="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded ml-2">Propriétaire</span>
                </label>
                <div class="border-2 border-dashed border-orange-200 rounded-xl p-4 text-center hover:border-orange-400 transition-colors cursor-pointer"
                    @click="$refs.titleInput.click()">
                    <div x-show="!titlePreview" class="py-3">
                        <svg class="w-8 h-8 text-orange-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500">Titre de propriété ou contrat de bail (optionnel mais recommandé)</p>
                    </div>
                    <img x-show="titlePreview" x-cloak :src="titlePreview" class="max-h-32 mx-auto rounded-lg object-contain">
                    <input type="file" name="title_deed" accept="image/*,application/pdf" x-ref="titleInput"
                        @change="previewFile($event.target, 'titlePreview')" class="hidden">
                </div>
            </div>
            @endif

            <button type="submit"
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md">
                Soumettre pour vérification
            </button>
        </form>
    </div>
    @endif

    <!-- Why verify section -->
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
        <h3 class="font-bold text-blue-900 text-lg mb-4">Pourquoi vérifier votre identité ?</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach([
                ['icon' => '🔒', 'title' => 'Sécurité renforcée', 'desc' => 'Protège la communauté contre les fraudes et arnaques'],
                ['icon' => '⭐', 'title' => 'Badge Vérifié', 'desc' => 'Votre profil affiche un badge de confiance visible par tous'],
                ['icon' => '🚀', 'title' => 'Plus de réservations', 'desc' => 'Les annonces vérifiées reçoivent 5× plus de demandes'],
                ['icon' => '💰', 'title' => 'Paiements débloqués', 'desc' => 'Accès complet aux paiements Mobile Money sans restrictions'],
            ] as $benefit)
            <div class="flex items-start gap-3">
                <span class="text-2xl flex-shrink-0">{{ $benefit['icon'] }}</span>
                <div>
                    <p class="font-semibold text-blue-900 text-sm">{{ $benefit['title'] }}</p>
                    <p class="text-blue-700 text-xs mt-0.5">{{ $benefit['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4 p-3 bg-white bg-opacity-60 rounded-xl">
            <p class="text-xs text-blue-700">
                <strong>Confidentialité :</strong> Vos documents sont chiffrés et stockés en sécurité. Ils ne sont utilisés que pour la vérification et ne sont jamais partagés avec des tiers.
            </p>
        </div>
    </div>

</div>
@endsection
