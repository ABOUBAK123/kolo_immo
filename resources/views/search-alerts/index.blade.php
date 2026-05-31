@extends('layouts.app')
@section('title', 'Mes alertes de recherche')
@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🔔 Mes alertes de recherche</h1>
            <p class="text-gray-500 text-sm mt-0.5">Soyez notifié quand de nouveaux biens correspondent à vos critères</p>
        </div>
        <a href="{{ route('properties.index') }}" class="text-sm text-blue-700 font-semibold hover:underline">
            ← Explorer
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Alerts list --}}
    @if($alerts->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-14 text-center">
        <p class="text-4xl mb-4">🔔</p>
        <h2 class="text-lg font-bold text-gray-900 mb-2">Aucune alerte configurée</h2>
        <p class="text-gray-500 text-sm mb-6">Créez une alerte depuis la page de recherche pour être notifié par email quand de nouveaux biens correspondent à vos critères.</p>
        <a href="{{ route('properties.index') }}"
           class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
            Rechercher des biens
        </a>
    </div>
    @else
    <div class="space-y-4">
        @foreach($alerts as $alert)
        <div class="bg-white rounded-2xl border {{ $alert->is_active ? 'border-gray-100' : 'border-gray-100 opacity-60' }} shadow-sm p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-bold text-gray-900">{{ $alert->name }}</h3>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                            {{ $alert->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $alert->is_active ? 'Active' : 'Pausée' }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-medium">
                            {{ $alert->frequency === 'daily' ? 'Quotidienne' : 'Hebdomadaire' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">🔍 {{ $alert->filterLabel() }}</p>
                    @if($alert->last_sent_at)
                    <p class="text-xs text-gray-400 mt-1">Dernier envoi : {{ $alert->last_sent_at->format('d/m/Y à H:i') }}</p>
                    @else
                    <p class="text-xs text-gray-400 mt-1">Jamais envoyée</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    {{-- Toggle --}}
                    <form action="{{ route('search-alerts.toggle', $alert) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors
                            {{ $alert->is_active ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                            {{ $alert->is_active ? '⏸ Pausez' : '▶ Activer' }}
                        </button>
                    </form>
                    {{-- Delete --}}
                    <form action="{{ route('search-alerts.destroy', $alert) }}" method="POST"
                          onsubmit="return confirm('Supprimer cette alerte ?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <p class="text-xs text-gray-400 text-center mt-6">Maximum 5 alertes. {{ $alerts->count() }}/5 utilisée(s).</p>
    @endif
</div>
@endsection
