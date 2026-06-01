@extends('layouts.app')
@section('title', 'Politique de confidentialité — Kolo Immo')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Politique de confidentialité</h1>
        <p class="text-gray-500 text-sm">Dernière mise à jour : {{ date('d/m/Y') }}</p>
    </div>

    <div class="space-y-6">

        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
            <p class="text-sm text-blue-800 leading-relaxed">
                Kolo Immo s'engage à protéger la vie privée de ses utilisateurs. Cette politique explique quelles données nous collectons, comment nous les utilisons et quels sont vos droits.
            </p>
        </div>

        @foreach([
            ['title' => '1. Données collectées', 'items' => [
                'Informations d\'identité : nom, prénom, date de naissance',
                'Coordonnées : email, numéro de téléphone, adresse',
                'Documents KYC : CNI, passeport (chiffrés et stockés de manière sécurisée)',
                'Données de paiement : référence de transaction (jamais les données bancaires brutes)',
                'Données de navigation : adresse IP, cookies de session, pages visitées',
            ]],
            ['title' => '2. Utilisation des données', 'items' => [
                'Création et gestion de votre compte utilisateur',
                'Traitement des réservations et des paiements',
                'Vérification d\'identité (KYC)',
                'Envoi de notifications (SMS, email, push) relatives à votre activité',
                'Amélioration de nos services et personnalisation de l\'expérience',
                'Respect de nos obligations légales et réglementaires',
            ]],
            ['title' => '3. Partage des données', 'items' => [
                'Avec les autres utilisateurs : nom, avis, informations de contact limitées lors d\'une réservation confirmée',
                'Avec nos prestataires : CinetPay (paiement), AfricasTalking (SMS), hébergeur',
                'Avec les autorités : uniquement sur réquisition judiciaire',
                'Nous ne vendons jamais vos données à des tiers',
            ]],
            ['title' => '4. Cookies', 'items' => [
                'Cookies de session : nécessaires au fonctionnement de la plateforme',
                'Cookies de préférence : langue, devise sélectionnée',
                'Cookies analytiques : mesure d\'audience anonymisée',
                'Vous pouvez désactiver les cookies non essentiels dans vos paramètres de navigateur',
            ]],
            ['title' => '5. Conservation des données', 'items' => [
                'Données de compte : conservées pendant toute la durée de votre inscription + 3 ans après suppression',
                'Données de transaction : 10 ans (obligations comptables)',
                'Documents KYC : 5 ans après la dernière transaction',
                'Cookies : 13 mois maximum',
            ]],
            ['title' => '6. Vos droits', 'items' => [
                'Droit d\'accès : obtenir une copie de vos données',
                'Droit de rectification : corriger des données inexactes',
                'Droit à l\'effacement : supprimer votre compte et vos données',
                'Droit d\'opposition : s\'opposer au traitement de vos données',
                'Pour exercer ces droits : privacy@koloimmo.com',
            ]],
        ] as $section)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ $section['title'] }}</h2>
            <ul class="space-y-2">
                @foreach($section['items'] as $item)
                <li class="flex items-start gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $item }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endforeach

        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center">
            <p class="text-sm text-gray-600">Pour toute question relative à vos données personnelles :</p>
            <p class="font-semibold text-gray-900 mt-1">privacy@koloimmo.com</p>
        </div>
    </div>
</div>
@endsection
