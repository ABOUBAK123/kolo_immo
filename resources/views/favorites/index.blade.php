@extends('layouts.app')
@section('title', 'Mes favoris')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                Mes favoris
            </h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ $favorites->total() }} bien{{ $favorites->total() > 1 ? 's' : '' }} sauvegardé{{ $favorites->total() > 1 ? 's' : '' }}</p>
        </div>
        <a href="{{ route('properties.index') }}" class="text-sm text-blue-700 font-semibold hover:underline">
            ← Explorer les biens
        </a>
    </div>

    @if($favorites->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-900 mb-2">Aucun favori pour l'instant</h2>
        <p class="text-gray-500 text-sm mb-6">Cliquez sur le ❤ d'un bien pour le retrouver ici.</p>
        <a href="{{ route('properties.index') }}"
           class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-semibold px-6 py-3 rounded-xl text-sm transition-colors">
            Découvrir les biens
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($favorites as $property)
        <div class="relative">
            @include('partials.property-card', ['property' => $property])
            {{-- Price drop alert --}}
            @if($property->pivot->price_at_save && $property->price_per_night < $property->pivot->price_at_save)
            <div class="absolute top-0 left-0 right-0 bg-green-500 text-white text-xs font-bold text-center py-1.5 rounded-t-2xl z-10">
                🎉 Prix baissé ! {{ number_format($property->pivot->price_at_save - $property->price_per_night, 0, ',', ' ') }} FCFA de moins
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="mt-8">{{ $favorites->links() }}</div>
    @endif
</div>
@endsection
