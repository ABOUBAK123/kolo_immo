@extends('layouts.app')

@section('title', 'Contact - Kolo Immo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Contactez-nous</h1>
        <p class="text-gray-500">Notre équipe est disponible pour répondre à toutes vos questions.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Contact info -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4">Nos coordonnées</h3>
                <div class="space-y-4">
                    @foreach([
                        ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'Email', 'value' => 'support@koloimmo.com'],
                        ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'label' => 'Téléphone', 'value' => '01 01 42 00 46 09'],
                        ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 'label' => 'Adresse', 'value' => 'Abidjan, Côte d\'Ivoire'],
                        ['icon' => 'M12 2C6.48 2 2 6.48 2 12c0 1.54.36 3 .97 4.29L2.5 20.5l8.3-2.47C13 20.64 14.5 21 16 21c5.52 0 10-4.48 10-10S21.52 2 16 2m0 18c-1.41 0-2.73-.35-3.88-.98l-.28-.15-2.89.86.86-2.89-.15-.28C4.35 14.73 4 13.41 4 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8z', 'label' => 'WhatsApp', 'value' => '<a href="https://wa.me/22510142004609" target="_blank" class="hover:underline">+225 01 01 42 00 46 09</a>'],
                    ] as $info)
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-blue-50 text-blue-700 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $info['icon'] }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">{{ $info['label'] }}</p>
                            <p class="text-sm font-medium text-gray-700">@if($info['label'] === 'WhatsApp'){!! $info['value'] !!}@else{{ $info['value'] }}@endif</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-blue-50 rounded-2xl p-6">
                <h3 class="font-bold text-gray-900 mb-2">Horaires</h3>
                <p class="text-gray-600 text-sm">Lundi – Vendredi : 8h – 18h</p>
                <p class="text-gray-600 text-sm">Samedi : 9h – 14h</p>
                <p class="text-gray-400 text-xs mt-2">Heure de Côte d'Ivoire (GMT+0)</p>
            </div>
        </div>

        <!-- Contact form -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-900 mb-4">Envoyez-nous un message</h3>
            @if(session('contact_sent'))
            <div class="bg-green-50 text-green-700 rounded-xl p-4 mb-4 text-sm font-medium">
                Votre message a été envoyé. Nous vous répondrons dans les 24h.
            </div>
            @endif
            <form action="#" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom complet</label>
                    <input type="text" name="name" value="{{ Auth::user()->name ?? '' }}" placeholder="Votre nom" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ Auth::user()->email ?? '' }}" placeholder="votre@email.com" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Sujet</label>
                    <select name="subject" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Choisissez un sujet</option>
                        <option value="reservation">Problème de réservation</option>
                        <option value="payment">Problème de paiement</option>
                        <option value="property">Signaler un logement</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                    <textarea name="message" rows="4" placeholder="Décrivez votre problème ou question..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-700 text-white font-semibold py-3 rounded-xl hover:bg-blue-800 transition-colors">
                    Envoyer le message
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
