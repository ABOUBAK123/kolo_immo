@extends('layouts.admin')

@section('title', 'Propriétaires')

@section('content')

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
    {{ session('error') }}
</div>
@endif

<!-- Filters -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
    <form action="{{ route('admin.owners.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-gray-500 mb-1">RECHERCHER</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email, téléphone..."
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">ACTIVATION</label>
            <select name="activation" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous</option>
                <option value="pending" {{ request('activation') === 'pending' ? 'selected' : '' }}>⏳ En attente d'activation</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">STATUT KYC</label>
            <select name="kyc" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous</option>
                <option value="verified" {{ request('kyc') === 'verified' ? 'selected' : '' }}>Vérifiés</option>
                <option value="pending"  {{ request('kyc') === 'pending'  ? 'selected' : '' }}>En attente</option>
                <option value="none"     {{ request('kyc') === 'none'     ? 'selected' : '' }}>Non soumis</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-4 py-2 rounded-lg text-sm transition-colors">
            Filtrer
        </button>
        <a href="{{ route('admin.owners.index') }}" class="border border-gray-200 text-gray-600 font-semibold px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors">
            Réinitialiser
        </a>
    </form>
</div>

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-900">Propriétaires ({{ $owners->total() ?? 0 }})</h2>
        <span class="text-sm text-gray-500">Page {{ $owners->currentPage() ?? 1 }} sur {{ $owners->lastPage() ?? 1 }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">ID</th>
                    <th class="text-left px-5 py-3">Propriétaire</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Contact</th>
                    <th class="text-center px-5 py-3">KYC</th>
                    <th class="text-center px-5 py-3 hidden lg:table-cell">Biens</th>
                    <th class="text-center px-5 py-3">Statut</th>
                    <th class="px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($owners ?? [] as $owner)
                <tr class="hover:bg-gray-50 transition-colors {{ $owner->is_banned ? 'opacity-60' : '' }}">
                    <td class="px-5 py-3 text-gray-400 text-xs font-mono">#{{ $owner->id }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background: linear-gradient(135deg, #EA580C, #FB923C);">
                                {{ strtoupper(substr($owner->name ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $owner->name }}</p>
                                <p class="text-xs text-gray-400">Inscrit {{ $owner->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        <p class="text-gray-700">{{ $owner->email ?? '—' }}</p>
                        <p class="text-gray-400 text-xs">{{ $owner->phone ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($owner->isKycVerified())
                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Vérifié
                        </span>
                        @elseif($owner->kycDocuments->where('status', 'pending')->isNotEmpty())
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full">En attente</span>
                        @else
                        <span class="bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-full">Non soumis</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center hidden lg:table-cell text-gray-700 font-medium">
                        {{ $owner->properties_count ?? 0 }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($owner->is_banned)
                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>Banni
                            </span>
                        @elseif(!$owner->is_active)
                            <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>En attente
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Actif
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('admin.users.show', $owner) }}" title="Voir"
                                class="w-8 h-8 inline-flex items-center justify-center text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                            @if(!$owner->is_banned)
                            <form action="{{ route('admin.users.toggle-active', $owner) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $owner->is_active ? 'Désactiver' : 'Activer' }}"
                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg transition-colors
                                    {{ $owner->is_active ? 'text-orange-700 bg-orange-50 hover:bg-orange-100' : 'text-green-700 bg-green-50 hover:bg-green-100' }}"
                                    onclick="return confirm('{{ $owner->is_active ? 'Désactiver' : 'Activer' }} ce compte propriétaire ?')">
                                    @if($owner->is_active)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.36 6.64a9 9 0 11-12.73 0M12 3v9"/>
                                    </svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @endif
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('admin.owners.edit', $owner) }}" title="Modifier"
                                class="w-8 h-8 inline-flex items-center justify-center text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('admin.owners.destroy', $owner) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" title="Supprimer"
                                    class="w-8 h-8 inline-flex items-center justify-center text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                    onclick="return confirm('Supprimer (archiver) ce propriétaire ?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                        Aucun propriétaire trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($owners) && $owners->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $owners->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
