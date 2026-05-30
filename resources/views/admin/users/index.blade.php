@extends('layouts.admin')

@section('title', 'Gestion des utilisateurs')

@section('content')

<!-- Filters -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
    <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-gray-500 mb-1">RECHERCHER</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email, téléphone..."
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">RÔLE</label>
            <select name="role" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les rôles</option>
                <option value="tenant" {{ request('role') === 'tenant' ? 'selected' : '' }}>Locataires</option>
                <option value="owner"  {{ request('role') === 'owner'  ? 'selected' : '' }}>Propriétaires</option>
                <option value="both"   {{ request('role') === 'both'   ? 'selected' : '' }}>Les deux</option>
                <option value="agent"  {{ request('role') === 'agent'  ? 'selected' : '' }}>Agents immobiliers</option>
                <option value="admin"  {{ request('role') === 'admin'  ? 'selected' : '' }}>Admins</option>
            </select>
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
        <a href="{{ route('admin.users.index') }}" class="border border-gray-200 text-gray-600 font-semibold px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors">
            Réinitialiser
        </a>
    </form>
</div>

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-900">Utilisateurs ({{ $users->total() ?? 0 }})</h2>
        <span class="text-sm text-gray-500">Page {{ $users->currentPage() ?? 1 }} sur {{ $users->lastPage() ?? 1 }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">ID</th>
                    <th class="text-left px-5 py-3">Utilisateur</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Contact</th>
                    <th class="text-center px-5 py-3">Rôle</th>
                    <th class="text-center px-5 py-3">KYC</th>
                    <th class="text-center px-5 py-3 hidden lg:table-cell">Biens</th>
                    <th class="text-center px-5 py-3 hidden lg:table-cell">Réservations</th>
                    <th class="text-center px-5 py-3">Statut</th>
                    <th class="px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users ?? [] as $user)
                <tr class="hover:bg-gray-50 transition-colors {{ $user->is_banned ? 'opacity-60' : '' }}">
                    <td class="px-5 py-3 text-gray-400 text-xs font-mono">#{{ $user->id }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                                {{ strtoupper(substr($user->prenom ?? $user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $user->prenom ?? '' }} {{ $user->name }}</p>
                                <p class="text-xs text-gray-400">Inscrit {{ $user->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        <p class="text-gray-700">{{ $user->email ?? '—' }}</p>
                        <p class="text-gray-400 text-xs">{{ $user->phone ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @php
                            $roleLabel = match($user->role) {
                                'admin'  => ['Admin',            'bg-red-100 text-red-800'],
                                'owner'  => ['Propriétaire',     'bg-orange-100 text-orange-800'],
                                'agent'  => ['Agent immo',       'bg-purple-100 text-purple-800'],
                                'both'   => ['Proprio/Locataire','bg-teal-100 text-teal-800'],
                                default  => ['Locataire',        'bg-blue-100 text-blue-800'],
                            };
                        @endphp
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleLabel[1] }}">
                            {{ $roleLabel[0] }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($user->isKycVerified())
                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Vérifié
                        </span>
                        @elseif($user->kycDocuments->where('status', 'pending')->isNotEmpty())
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full">En attente</span>
                        @else
                        <span class="bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-full">Non soumis</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center hidden lg:table-cell text-gray-700 font-medium">
                        {{ $user->properties_count ?? 0 }}
                    </td>
                    <td class="px-5 py-3 text-center hidden lg:table-cell text-gray-700 font-medium">
                        {{ $user->bookings_count ?? 0 }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($user->is_banned)
                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>Banni
                            </span>
                        @elseif(!$user->is_active)
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
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('admin.users.show', $user) }}"
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 transition-colors">
                                Voir
                            </a>
                            @if(!$user->is_banned)
                            <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="text-xs font-semibold px-2 py-1 rounded transition-colors
                                    {{ $user->is_active ? 'text-orange-700 bg-orange-50 hover:bg-orange-100' : 'text-green-700 bg-green-50 hover:bg-green-100' }}"
                                    onclick="return confirm('{{ $user->is_active ? 'Désactiver' : 'Activer' }} ce compte ?')">
                                    {{ $user->is_active ? 'Désactiver' : '✓ Activer' }}
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.users.toggle-ban', $user) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="text-xs font-medium px-2 py-1 rounded transition-colors
                                    {{ $user->is_banned ? 'text-green-700 bg-green-50 hover:bg-green-100' : 'text-red-700 bg-red-50 hover:bg-red-100' }}"
                                    onclick="return confirm('{{ $user->is_banned ? 'Débannir' : 'Bannir' }} cet utilisateur ?')">
                                    {{ $user->is_banned ? 'Débannir' : 'Bannir' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-10 text-center text-gray-400">
                        Aucun utilisateur trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($users) && $users->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
