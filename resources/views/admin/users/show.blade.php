@extends('layouts.admin')

@section('title', 'Utilisateur : ' . $user->name)

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline text-sm">← Utilisateurs</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 font-medium">{{ $user->name }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-3xl font-bold mb-3"
                 style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
            <p class="text-gray-500 text-sm">{{ $user->email ?? $user->phone }}</p>
            <div class="flex gap-2 mt-2">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' : ($user->role === 'owner' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700') }}">
                    {{ ['tenant' => 'Locataire', 'owner' => 'Propriétaire', 'both' => 'Prop. & Locataire', 'admin' => 'Admin'][$user->role] ?? $user->role }}
                </span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $user->kyc_status === 'verified' ? 'bg-green-100 text-green-700' : ($user->kyc_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    KYC: {{ ['pending' => 'En attente', 'verified' => 'Vérifié', 'rejected' => 'Rejeté'][$user->kyc_status] ?? $user->kyc_status }}
                </span>
            </div>
        </div>

        <div class="space-y-3 text-sm">
            @if($user->phone)
            <div class="flex justify-between"><span class="text-gray-500">Téléphone</span><span class="font-medium">{{ $user->phone }}</span></div>
            @endif
            @if($user->email)
            <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium truncate max-w-32">{{ $user->email }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-gray-500">Pays</span><span class="font-medium">{{ $user->country }}</span></div>
            @if($user->city)
            <div class="flex justify-between"><span class="text-gray-500">Ville</span><span class="font-medium">{{ $user->city }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-gray-500">Score confiance</span><span class="font-medium">{{ $user->trust_score }}/100</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Inscrit le</span><span class="font-medium">{{ $user->created_at->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Statut</span>
                <span class="{{ $user->is_banned ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold' }}">
                    {{ $user->is_banned ? 'Banni' : 'Actif' }}
                </span>
            </div>
        </div>

        @if(!$user->isAdmin())
        <form method="POST" action="{{ route('admin.users.toggle-ban', $user) }}" class="mt-6">
            @csrf
            <button type="submit"
                class="w-full py-2 px-4 rounded-lg text-sm font-semibold transition
                    {{ $user->is_banned ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}"
                onclick="return confirm('Confirmer cette action ?')">
                {{ $user->is_banned ? 'Débannir l\'utilisateur' : 'Bannir l\'utilisateur' }}
            </button>
        </form>
        @endif
    </div>

    <!-- Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Properties -->
        @if($user->properties->count() > 0)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-900">
                Logements ({{ $user->properties->count() }})
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($user->properties->take(5) as $property)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $property->title }}</p>
                        <p class="text-xs text-gray-400">{{ $property->city }} · {{ number_format($property->price_per_night, 0, ',', ' ') }} FCFA/nuit</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                        {{ $property->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $property->status }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- KYC Documents -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-900">
                Documents KYC ({{ $user->kycDocuments->count() }})
            </div>
            @forelse($user->kycDocuments as $doc)
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $doc->type)) }}</p>
                    <p class="text-xs text-gray-400">Soumis le {{ $doc->created_at->format('d/m/Y') }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                    {{ $doc->status === 'approved' ? 'bg-green-100 text-green-700' : ($doc->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'][$doc->status] ?? $doc->status }}
                </span>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-gray-400 text-sm">Aucun document soumis</div>
            @endforelse
        </div>

        <!-- Bookings summary -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-900">
                Réservations locataire ({{ $user->bookingsAsTenant->count() }}) · Propriétaire ({{ $user->bookingsAsOwner->count() }})
            </div>
            <div class="px-5 py-4 text-sm text-gray-500">
                @if($user->bookingsAsTenant->isEmpty() && $user->bookingsAsOwner->isEmpty())
                    Aucune réservation
                @else
                    <p>Total en tant que locataire : <strong>{{ $user->bookingsAsTenant->count() }} réservation(s)</strong></p>
                    <p>Total en tant que propriétaire : <strong>{{ $user->bookingsAsOwner->count() }} réservation(s)</strong></p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
