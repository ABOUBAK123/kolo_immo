@extends('layouts.app')
@section('title', "Conditions générales d'utilisation — Kolo Immo")
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Conditions générales d'utilisation</h1>
        <p class="text-gray-500 text-sm">Dernière mise à jour : {{ date('d/m/Y') }}</p>
    </div>

    <div class="prose prose-gray max-w-none space-y-8">

        @foreach([
            ['title' => '1. Objet', 'content' => "Les présentes conditions générales d'utilisation (CGU) régissent l'accès et l'utilisation de la plateforme Kolo Immo, accessible à l'adresse koloimmo.com, ainsi que de l'application mobile associée. En accédant à la plateforme, vous acceptez sans réserve les présentes CGU."],
            ['title' => '2. Description du service', 'content' => "Kolo Immo est une plateforme de mise en relation entre propriétaires de résidences meublées et locataires en Afrique de l'Ouest. Kolo Immo agit en tant qu'intermédiaire et n'est pas partie au contrat de location conclu entre le propriétaire et le locataire."],
            ['title' => '3. Inscription et compte utilisateur', 'content' => "L'inscription est obligatoire pour accéder aux fonctionnalités de réservation et de publication. Vous vous engagez à fournir des informations exactes, complètes et à jour. Toute tentative de création d'un compte frauduleux entraîne la suppression immédiate du compte et peut faire l'objet de poursuites."],
            ['title' => '4. Vérification KYC', 'content' => "Pour publier un bien ou effectuer une réservation, une vérification d'identité (KYC) peut être requise. Vous acceptez de fournir les documents demandés (CNI, passeport, etc.) et autorisez Kolo Immo à vérifier l'authenticité de ces documents."],
            ['title' => '5. Réservations et paiements', 'content' => "Les paiements sont traités via notre prestataire CinetPay (Orange Money, Wave, MTN MoMo, Moov Money). Les fonds sont placés en séquestre par Kolo Immo jusqu'à la confirmation du séjour. Kolo Immo prélève des frais de service de " . config('kolo.service_fee_percent') . "% sur le montant de la réservation."],
            ['title' => '6. Annulations et remboursements', 'content' => "La politique d'annulation applicable est celle définie par le propriétaire pour chaque bien (Flexible, Modérée ou Stricte). En cas d'annulation par le propriétaire, le locataire est remboursé intégralement dans un délai de 5 à 10 jours ouvrés."],
            ['title' => '7. Responsabilités', 'content' => "Kolo Immo ne peut être tenu responsable des dommages causés par les utilisateurs entre eux, ni de l'exactitude des informations publiées par les propriétaires. Chaque utilisateur est responsable de ses actes sur la plateforme."],
            ['title' => '8. Propriété intellectuelle', 'content' => "Tous les contenus de la plateforme (logos, textes, images, code) sont la propriété exclusive de Kolo Immo. Toute reproduction sans autorisation est interdite."],
            ['title' => '9. Modification des CGU', 'content' => "Kolo Immo se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés par email ou notification dans l'application. L'utilisation continue de la plateforme après modification vaut acceptation des nouvelles CGU."],
            ['title' => '10. Loi applicable', 'content' => "Les présentes CGU sont soumises au droit applicable dans le pays de résidence de l'utilisateur. En cas de litige, les parties s'engagent à chercher une solution amiable avant tout recours judiciaire."],
        ] as $section)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">{{ $section['title'] }}</h2>
            <p class="text-gray-600 text-sm leading-relaxed">{{ $section['content'] }}</p>
        </div>
        @endforeach

    </div>

    <div class="mt-10 bg-blue-50 border border-blue-200 rounded-2xl p-6 text-center">
        <p class="text-sm text-blue-700 font-medium">Des questions sur nos conditions ?</p>
        <a href="{{ route('contact') }}" class="inline-block mt-3 bg-blue-600 text-white font-bold px-5 py-2.5 rounded-xl text-sm hover:bg-blue-700 transition">
            Nous contacter
        </a>
    </div>
</div>
@endsection
