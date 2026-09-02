@extends('layouts.admin')

@section('title', 'Logements')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-900">Logements</h1>
    @php $pendingVerif = \App\Models\Property::where('verification_status', 'pending')->count(); @endphp
    @if($pendingVerif > 0)
    <span class="inline-flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 text-sm font-semibold px-3 py-1.5 rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        {{ $pendingVerif }} logement(s) en attente de vérification
    </span>
    @endif
</div>

<!-- Filters -->
<form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Recherche</label>
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Titre, ville..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-48 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">Tous</option>
            @foreach(['draft' => 'Brouillon', 'active' => 'Actif', 'inactive' => 'Inactif', 'suspended' => 'Suspendu'] as $v => $l)
            <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Vérification</label>
        <select name="verification" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">Toutes</option>
            @foreach(['pending' => 'En attente', 'under_review' => 'En examen', 'verified' => 'Vérifié', 'rejected' => 'Rejeté'] as $v => $l)
            <option value="{{ $v }}" {{ request('verification') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Filtrer</button>
    @if(request()->hasAny(['search', 'status', 'type', 'verification']))
    <a href="{{ route('admin.properties.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Réinitialiser</a>
    @endif
</form>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ rejectModal: null, rejectNotes: '' }">
    <div class="overflow-x-scroll" style="scrollbar-width: auto; scrollbar-color: #cbd5e1 #f1f5f9;">
        <table class="w-full text-sm" style="min-width: 1100px;">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase">
                    <th class="text-left px-5 py-3">Logement</th>
                    <th class="text-left px-5 py-3">Propriétaire</th>
                    <th class="text-right px-5 py-3">Prix/nuit</th>
                    <th class="text-center px-5 py-3">Réservations</th>
                    <th class="text-center px-5 py-3">Statut</th>
                    <th class="text-center px-5 py-3">Vérification</th>
                    <th class="text-center px-5 py-3">⭐</th>
                    <th class="text-center px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($properties as $property)
                @php
                $statusColors = ['draft' => 'bg-gray-100 text-gray-600', 'active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-yellow-100 text-yellow-700', 'suspended' => 'bg-red-100 text-red-700'];
                $statusLabels = ['draft' => 'Brouillon', 'active' => 'Actif', 'inactive' => 'Inactif', 'suspended' => 'Suspendu'];
                $verifColors  = ['pending' => 'bg-amber-100 text-amber-700', 'under_review' => 'bg-blue-100 text-blue-700', 'verified' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'];
                $verifLabels  = ['pending' => 'En attente', 'under_review' => 'En examen', 'verified' => '✓ Vérifié', 'rejected' => '✗ Rejeté'];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ Str::limit($property->title, 36) }}</p>
                        <p class="text-xs text-gray-400">{{ $property->city }}, {{ $property->country }}</p>
                        @if($property->verification_notes && in_array($property->verification_status, ['rejected', 'under_review']))
                        <p class="text-xs text-amber-600 mt-0.5 max-w-xs truncate" title="{{ $property->verification_notes }}">
                            💬 {{ Str::limit($property->verification_notes, 40) }}
                        </p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-700">{{ $property->owner->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-semibold">{{ number_format($property->price_per_night, 0, ',', ' ') }}</td>
                    <td class="px-5 py-3 text-center text-gray-600">{{ $property->bookings_count }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$property->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $statusLabels[$property->status] ?? $property->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $verifColors[$property->verification_status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $verifLabels[$property->verification_status] ?? $property->verification_status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <form method="POST" action="{{ route('admin.properties.toggle-featured', $property) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xl leading-none" title="{{ $property->is_featured ? 'Retirer' : 'Mettre en avant' }}">
                                {{ $property->is_featured ? '⭐' : '☆' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-center gap-1.5 flex-wrap">

                            {{-- Verification workflow --}}
                            @if($property->verification_status === 'pending')
                            <form method="POST" action="{{ route('admin.properties.under-review', $property) }}" class="inline">
                                @csrf
                                <button title="Examiner"
                                    class="w-8 h-8 inline-flex items-center justify-center text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </button>
                            </form>
                            @endif

                            @if(in_array($property->verification_status, ['pending', 'under_review', 'rejected']))
                            <form method="POST" action="{{ route('admin.properties.verify', $property) }}" class="inline">
                                @csrf
                                <button title="Approuver"
                                    class="w-8 h-8 inline-flex items-center justify-center text-green-700 bg-green-100 hover:bg-green-200 rounded-lg transition-colors"
                                    onclick="return confirm('Approuver ce logement ?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                            @endif

                            @if(in_array($property->verification_status, ['pending', 'under_review', 'verified']))
                            <button type="button" title="Rejeter"
                                @click="rejectModal = {{ $property->id }}; rejectNotes = ''"
                                class="w-8 h-8 inline-flex items-center justify-center text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            @endif

                            {{-- Divider --}}
                            <span class="text-gray-200">|</span>

                            {{-- Status toggle --}}
                            <form method="POST" action="{{ route('admin.properties.toggle-status', $property) }}" class="inline">
                                @csrf
                                @if($property->status === 'suspended')
                                <button title="Réactiver"
                                    class="w-8 h-8 inline-flex items-center justify-center text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors"
                                    onclick="return confirm('Réactiver ce logement ?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                                @elseif($property->status === 'active')
                                <button title="Désactiver"
                                    class="w-8 h-8 inline-flex items-center justify-center text-yellow-700 bg-yellow-100 hover:bg-yellow-200 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                                @else
                                <button title="Activer"
                                    class="w-8 h-8 inline-flex items-center justify-center text-green-700 bg-green-100 hover:bg-green-200 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                                @endif
                            </form>

                            @if($property->status !== 'suspended')
                            <form method="POST" action="{{ route('admin.properties.suspend', $property) }}" class="inline">
                                @csrf
                                <button title="Suspendre"
                                    class="w-8 h-8 inline-flex items-center justify-center text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-colors"
                                    onclick="return confirm('Suspendre ce logement ?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636a9 9 0 0012.728 12.728zM5.636 5.636l12.728 12.728"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>

                        {{-- Reject modal for this row --}}
                        <div x-show="rejectModal === {{ $property->id }}" x-cloak
                            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
                            @click.self="rejectModal = null">
                            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
                                <h3 class="font-bold text-gray-900 mb-3">Rejeter le logement</h3>
                                <p class="text-sm text-gray-500 mb-4">{{ Str::limit($property->title, 50) }}</p>
                                <form method="POST" action="{{ route('admin.properties.reject', $property) }}">
                                    @csrf
                                    <textarea name="notes" x-model="rejectNotes" rows="3" required
                                        placeholder="Raison du rejet (visible par le propriétaire)..."
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none mb-4"></textarea>
                                    <div class="flex gap-3">
                                        <button type="button" @click="rejectModal = null"
                                            class="flex-1 px-4 py-2 border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition">
                                            Annuler
                                        </button>
                                        <button type="submit"
                                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
                                            Confirmer le rejet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">Aucun logement trouvé</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $properties->links() }}
    </div>
</div>
@endsection
