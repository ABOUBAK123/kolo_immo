@extends('layouts.admin')

@section('title', 'Logements')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-900">Logements</h1>
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
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Filtrer</button>
    @if(request()->hasAny(['search', 'status', 'type']))
    <a href="{{ route('admin.properties.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Réinitialiser</a>
    @endif
</form>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase">
                    <th class="text-left px-5 py-3">Logement</th>
                    <th class="text-left px-5 py-3">Propriétaire</th>
                    <th class="text-right px-5 py-3">Prix/nuit</th>
                    <th class="text-center px-5 py-3">Réservations</th>
                    <th class="text-center px-5 py-3">Avis</th>
                    <th class="text-center px-5 py-3">Statut</th>
                    <th class="text-center px-5 py-3">Mis en avant</th>
                    <th class="text-center px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($properties as $property)
                @php
                $statusColors = ['draft' => 'bg-gray-100 text-gray-600', 'active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-yellow-100 text-yellow-700', 'suspended' => 'bg-red-100 text-red-700'];
                $statusLabels = ['draft' => 'Brouillon', 'active' => 'Actif', 'inactive' => 'Inactif', 'suspended' => 'Suspendu'];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ Str::limit($property->title, 40) }}</p>
                        <p class="text-xs text-gray-400">{{ $property->city }}, {{ $property->country }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-700">{{ $property->owner->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-semibold">{{ number_format($property->price_per_night, 0, ',', ' ') }} FCFA</td>
                    <td class="px-5 py-3 text-center text-gray-600">{{ $property->bookings_count }}</td>
                    <td class="px-5 py-3 text-center text-gray-600">{{ $property->reviews_count }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$property->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $statusLabels[$property->status] ?? $property->status }}
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
                    <td class="px-5 py-3 text-center">
                        @if($property->status !== 'suspended')
                        <form method="POST" action="{{ route('admin.properties.suspend', $property) }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded font-semibold hover:bg-red-200 transition"
                                onclick="return confirm('Suspendre ce logement ?')">
                                Suspendre
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">Suspendu</span>
                        @endif
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
