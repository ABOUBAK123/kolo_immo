@extends('layouts.app')
@section('title', 'FAQ — Questions fréquentes — Kolo Immo')
@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Questions fréquentes</h1>
        <p class="text-gray-500">Tout ce que vous devez savoir sur Kolo Immo.</p>
    </div>

    @php
    $faqs = [
        'Pour les locataires' => [
            ['q' => 'Comment réserver un logement ?', 'a' => 'Parcourez les annonces, choisissez un logement, sélectionnez vos dates et cliquez sur "Réserver". Vous serez guidé à travers le processus de paiement sécurisé.'],
            ['q' => 'Quels modes de paiement sont acceptés ?', 'a' => 'Nous acceptons Orange Money, Wave, MTN MoMo et Moov Money via notre partenaire CinetPay. Les paiements par carte bancaire ne sont pas encore disponibles.'],
            ['q' => 'Les fonds sont-ils sécurisés ?', 'a' => 'Oui. Votre paiement est placé en séquestre par Kolo Immo et n\'est libéré au propriétaire qu\'après confirmation de votre check-in. En cas de problème, vous êtes protégé.'],
            ['q' => 'Puis-je annuler ma réservation ?', 'a' => 'Oui, selon la politique d\'annulation du logement (Flexible, Modérée ou Stricte). Vérifiez la politique avant de réserver. Les remboursements sont traités sous 5-10 jours ouvrés.'],
            ['q' => 'Comment contacter le propriétaire ?', 'a' => 'Une fois votre réservation créée, une messagerie interne est activée. Vous pouvez échanger directement avec le propriétaire depuis la page de votre réservation.'],
        ],
        'Pour les propriétaires' => [
            ['q' => 'Comment publier mon logement ?', 'a' => 'Créez un compte propriétaire, complétez votre vérification KYC, puis cliquez sur "Publier un bien". Le formulaire vous guide en 7 étapes : type, localisation, caractéristiques, tarifs, équipements, règles et photos.'],
            ['q' => 'Quels sont les frais pour les propriétaires ?', 'a' => 'Kolo Immo prélève une commission de ' . config('kolo.platform_commission_percent') . '% sur chaque réservation confirmée, déduite lors de la libération des fonds. Les frais de service (' . config('kolo.service_fee_percent') . '%) sont à la charge des locataires.'],
            ['q' => 'Quand suis-je payé ?', 'a' => 'Les fonds sont libérés après confirmation du check-in du locataire. Le virement est effectué sur votre portefeuille Kolo Immo, depuis lequel vous pouvez retirer vers votre mobile money.'],
            ['q' => 'Puis-je bloquer des dates ?', 'a' => 'Oui, depuis votre espace propriétaire, rendez-vous dans "Disponibilités" pour bloquer des périodes (maintenance, usage personnel, etc.).'],
            ['q' => 'Qu\'est-ce que la vérification KYC ?', 'a' => 'La vérification KYC (Know Your Customer) consiste à confirmer votre identité avec un document officiel. Elle est requise pour publier un bien et garantit la sécurité pour tous les utilisateurs.'],
        ],
        'Technique et sécurité' => [
            ['q' => 'Mes données sont-elles sécurisées ?', 'a' => 'Oui. Toutes les communications sont chiffrées (HTTPS). Les documents KYC sont stockés de manière sécurisée. Consultez notre politique de confidentialité pour plus de détails.'],
            ['q' => 'Comment fonctionne le système d\'avis ?', 'a' => 'Après chaque séjour, le locataire peut noter le logement sur 6 critères (propreté, communication, précision, emplacement, valeur, note globale). Le propriétaire peut également évaluer le locataire.'],
            ['q' => 'Que faire en cas de litige ?', 'a' => 'Contactez d\'abord l\'autre partie via la messagerie. Si aucun accord n\'est trouvé, ouvrez un litige depuis votre espace. Notre équipe arbitre le dossier sous 5 jours ouvrés.'],
        ],
    ];
    @endphp

    @foreach($faqs as $category => $questions)
    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 bg-blue-600 rounded-full flex-shrink-0"></span>
            {{ $category }}
        </h2>
        <div class="space-y-3" x-data="{ open: null }">
            @foreach($questions as $i => $faq)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                    <span class="font-semibold text-gray-900 text-sm pr-4">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === {{ $i }}" x-collapse x-cloak class="px-5 pb-4">
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="bg-gradient-to-r from-blue-700 to-indigo-700 rounded-2xl p-6 text-white text-center mt-10">
        <p class="font-bold mb-1">Vous n'avez pas trouvé votre réponse ?</p>
        <p class="text-blue-200 text-sm mb-4">Notre équipe répond sous 24h. Vous pouvez aussi nous appeler au <a href="tel:010142004609" class="font-semibold underline">01 01 42 00 46 09</a>.</p>
        <a href="{{ route('contact') }}" class="inline-block bg-white text-blue-700 font-bold px-5 py-2.5 rounded-xl text-sm hover:bg-blue-50 transition">
            Nous contacter
        </a>
    </div>
</div>
@endsection
