@extends('layouts.app')

@section('title', 'À propos - Kolo Immo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">À propos de Kolo Immo</h1>
        <p class="text-xl text-gray-500 max-w-2xl mx-auto">La plateforme de référence pour la location de résidences meublées en Afrique de l'Ouest.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
        @foreach([
            ['icon' => '🏠', 'title' => 'Notre mission', 'text' => 'Simplifier la location de logements meublés de qualité pour les voyageurs et professionnels en Afrique de l\'Ouest.'],
            ['icon' => '🤝', 'title' => 'Notre engagement', 'text' => 'Garantir des transactions sécurisées, des logements vérifiés et un support disponible 7j/7.'],
            ['icon' => '🌍', 'title' => 'Notre présence', 'text' => 'Disponible en Côte d\'Ivoire, Sénégal, Burkina Faso, Mali, Bénin et Togo.'],
        ] as $item)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
            <div class="text-4xl mb-4">{{ $item['icon'] }}</div>
            <h3 class="font-bold text-gray-900 mb-2">{{ $item['title'] }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">{{ $item['text'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-gradient-to-br from-blue-700 to-indigo-800 rounded-3xl p-10 text-white text-center">
        <h2 class="text-2xl font-bold mb-3">Rejoignez Kolo Immo</h2>
        <p class="text-blue-200 mb-6">Des milliers de voyageurs et propriétaires nous font confiance.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="bg-white text-blue-700 font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors">Créer un compte</a>
            <a href="{{ route('properties.index') }}" class="border border-white text-white font-bold px-6 py-3 rounded-xl hover:bg-white hover:bg-opacity-10 transition-colors">Voir les logements</a>
        </div>
    </div>
</div>
@endsection
