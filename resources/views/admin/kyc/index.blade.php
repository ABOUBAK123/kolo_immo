@extends('layouts.admin')

@section('title', 'Vérification KYC')

@section('content')

<!-- Stats bar -->
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        ['label' => 'En attente', 'value' => $stats['pending'] ?? 0, 'color' => 'border-yellow-200 bg-yellow-50', 'text' => 'text-yellow-800'],
        ['label' => 'Approuvés', 'value' => $stats['approved'] ?? 0, 'color' => 'border-green-200 bg-green-50', 'text' => 'text-green-800'],
        ['label' => 'Rejetés', 'value' => $stats['rejected'] ?? 0, 'color' => 'border-red-200 bg-red-50', 'text' => 'text-red-800'],
    ] as $s)
    <div class="rounded-xl border {{ $s['color'] }} p-4 text-center">
        <p class="text-2xl font-bold {{ $s['text'] }}">{{ $s['value'] }}</p>
        <p class="text-sm font-medium {{ $s['text'] }}">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>

<!-- Filter tabs -->
<div class="flex gap-2 mb-4">
    @foreach(['pending' => 'En attente', 'approved' => 'Approuvés', 'rejected' => 'Rejetés', '' => 'Tous'] as $val => $label)
    <a href="{{ route('admin.kyc.index', ['status' => $val]) }}"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors
        {{ request('status', 'pending') === $val ? 'bg-blue-700 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<!-- KYC List -->
<div class="space-y-4">
    @forelse($kycDocuments ?? [] as $kyc)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ rejectModal: false }">
        <div class="flex flex-wrap items-start gap-4 p-5">

            <!-- User info -->
            <div class="flex items-start gap-3 flex-1 min-w-48">
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                    {{ strtoupper(substr($kyc->user->prenom ?? $kyc->user->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-900">{{ $kyc->user->prenom ?? '' }} {{ $kyc->user->name }}</p>
                    <p class="text-gray-500 text-sm">{{ $kyc->user->email }}</p>
                    <p class="text-gray-400 text-xs">{{ $kyc->user->phone }}</p>
                    <span class="inline-block mt-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $kyc->user->role === 'owner' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $kyc->user->role === 'owner' ? 'Propriétaire' : 'Locataire' }}
                    </span>
                </div>
            </div>

            <!-- Document info -->
            <div class="flex-1 min-w-48">
                <p class="text-xs font-semibold text-gray-500 mb-2">DOCUMENT</p>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded font-medium">
                            {{ ['cni' => 'CNI', 'passeport' => 'Passeport', 'carte_sejour' => 'Carte de séjour'][$kyc->document_type] ?? ucfirst($kyc->document_type) }}
                        </span>
                    </div>
                    @if($kyc->document_path)
                    <a href="{{ asset('storage/' . $kyc->document_path) }}" target="_blank"
                        class="flex items-center gap-2 text-blue-700 text-xs font-semibold hover:text-blue-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Voir le document
                    </a>
                    @endif
                    @if($kyc->selfie_path)
                    <a href="{{ asset('storage/' . $kyc->selfie_path) }}" target="_blank"
                        class="flex items-center gap-2 text-blue-700 text-xs font-semibold hover:text-blue-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Voir le selfie
                    </a>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-2">Soumis {{ $kyc->created_at->diffForHumans() }}</p>
            </div>

            <!-- Status -->
            <div class="flex-shrink-0">
                @php
                $statusColors = [
                    'pending'  => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                ];
                $statusLabels = ['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'];
                @endphp
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $statusColors[$kyc->status] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $statusLabels[$kyc->status] ?? ucfirst($kyc->status) }}
                </span>
                @if($kyc->rejection_reason)
                <p class="text-xs text-red-600 mt-1 max-w-40">Motif: {{ $kyc->rejection_reason }}</p>
                @endif
            </div>

            <!-- Actions -->
            @if($kyc->status === 'pending')
            <div class="flex items-center gap-2 flex-shrink-0">
                <form action="{{ route('admin.kyc.approve', $kyc) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition-colors"
                        onclick="return confirm('Approuver ce document KYC ?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approuver
                    </button>
                </form>

                <button type="button" @click="rejectModal = true"
                    class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Rejeter
                </button>
            </div>
            @endif
        </div>

        <!-- Rejection Modal (Alpine.js) -->
        <div x-show="rejectModal" x-cloak @click.self="rejectModal = false"
            class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Motif de rejet</h3>
                    <button @click="rejectModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mb-4">Veuillez indiquer la raison du rejet. L'utilisateur sera notifié.</p>
                <form action="{{ route('admin.kyc.reject', $kyc) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Raison du rejet</label>
                        <select name="rejection_reason" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500 mb-2" required>
                            <option value="">Sélectionner une raison...</option>
                            <option value="Document illisible">Document illisible ou flou</option>
                            <option value="Document expiré">Document expiré</option>
                            <option value="Document non conforme">Document non conforme</option>
                            <option value="Selfie non conforme">Selfie non conforme</option>
                            <option value="Identité non correspondante">Identité non correspondante</option>
                            <option value="Document falsifié">Document potentiellement falsifié</option>
                        </select>
                        <textarea name="rejection_details" rows="3" placeholder="Précisions supplémentaires (optionnel)..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="rejectModal = false"
                            class="flex-1 border border-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                            Annuler
                        </button>
                        <button type="submit"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                            Confirmer le rejet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h3 class="font-bold text-gray-700 mb-2">Aucun document KYC</h3>
        <p class="text-gray-400 text-sm">Aucun document correspondant au filtre sélectionné.</p>
    </div>
    @endforelse
</div>

@if(isset($kycDocuments) && $kycDocuments->hasPages())
<div class="mt-4">{{ $kycDocuments->withQueryString()->links() }}</div>
@endif

@endsection
