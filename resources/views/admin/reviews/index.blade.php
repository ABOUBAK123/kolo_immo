@extends('layouts.admin')
@section('title', 'Modération des avis signalés')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
        <p class="text-2xl font-bold text-red-600">{{ $stats['flagged_total'] }}</p>
        <p class="text-xs font-semibold text-red-500 mt-0.5">Total signalés</p>
    </div>
    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
        <p class="text-2xl font-bold text-amber-600">{{ $stats['tenant_to_property_count'] }}</p>
        <p class="text-xs font-semibold text-amber-500 mt-0.5">Avis logements</p>
    </div>
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $stats['owner_to_tenant_count'] }}</p>
        <p class="text-xs font-semibold text-blue-500 mt-0.5">Évaluations locataires</p>
    </div>
</div>

{{-- Filtres --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <select name="type" onchange="this.form.submit()"
        class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
        <option value="">Tous les types</option>
        <option value="tenant_to_property" @selected(request('type') === 'tenant_to_property')>Avis sur logements</option>
        <option value="owner_to_tenant"    @selected(request('type') === 'owner_to_tenant')>Évaluations locataires</option>
    </select>
</form>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

{{-- Liste des avis signalés --}}
@if($flaggedReviews->isEmpty())
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
    <svg class="w-12 h-12 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-lg font-semibold text-gray-700">Aucun avis signalé</p>
    <p class="text-gray-400 text-sm mt-1">La file de modération est vide.</p>
</div>
@else
<div class="space-y-4">
    @foreach($flaggedReviews as $review)
    <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="flex-1 min-w-0">

                {{-- Badges type + date --}}
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $review->type === 'tenant_to_property' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $review->type === 'tenant_to_property' ? 'Avis logement' : 'Éval. locataire' }}
                    </span>
                    <span class="text-xs text-gray-400">Signalé le {{ $review->updated_at->format('d/m/Y H:i') }}</span>
                    <span class="flex items-center gap-1 text-xs font-bold text-yellow-600 bg-yellow-50 px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        {{ number_format($review->rating_overall, 1) }}/5
                    </span>
                </div>

                {{-- Auteur + cible --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3 text-sm">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-0.5">Auteur de l'avis</p>
                        <p class="font-semibold text-gray-800">{{ $review->reviewer->name ?? '—' }}</p>
                        <p class="text-gray-500 text-xs">{{ $review->reviewer->email ?? '' }}</p>
                    </div>
                    @if($review->type === 'tenant_to_property')
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-0.5">Logement concerné</p>
                        <p class="font-semibold text-gray-800">{{ $review->property->title ?? '—' }}</p>
                    </div>
                    @else
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-0.5">Locataire évalué</p>
                        <p class="font-semibold text-gray-800">{{ $review->reviewee->name ?? '—' }}</p>
                    </div>
                    @endif
                </div>

                {{-- Motif du signalement --}}
                @if($review->flag_reason)
                <div class="bg-red-50 border border-red-100 rounded-xl p-3 mb-3">
                    <p class="text-xs font-semibold text-red-600 mb-1">Motif du signalement</p>
                    <p class="text-sm text-red-700">{{ $review->flag_reason }}</p>
                </div>
                @endif

                {{-- Contenu de l'avis --}}
                <div class="bg-white border border-gray-100 rounded-xl p-3">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Contenu de l'avis</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $review->comment }}</p>
                </div>

                {{-- Sous-notes --}}
                @php
                    $subRatings = array_filter([
                        'Propreté'      => $review->rating_cleanliness,
                        'Communication' => $review->rating_communication,
                        'Conformité'    => $review->rating_accuracy,
                        'Localisation'  => $review->rating_location,
                        'Rapport Q/P'   => $review->rating_value,
                        'Paiement'      => $review->rating_payment,
                    ]);
                @endphp
                @if(!empty($subRatings))
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($subRatings as $label => $note)
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $label }}: {{ number_format($note, 1) }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex sm:flex-col gap-2 sm:w-40 flex-shrink-0">
                <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="flex-1 sm:flex-none">
                    @csrf
                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
                        ✓ Approuver
                    </button>
                </form>
                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="flex-1 sm:flex-none"
                    onsubmit="return confirm('Supprimer définitivement cet avis ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
                        ✕ Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">{{ $flaggedReviews->links() }}</div>
@endif

@endsection
