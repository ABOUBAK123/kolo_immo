@extends('layouts.admin')

@section('title', 'Vérification KYC')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-900">Vérification KYC</h1>
</div>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        ['label' => 'En attente', 'value' => $stats['pending'],  'color' => 'border-yellow-200 bg-yellow-50', 'text' => 'text-yellow-800'],
        ['label' => 'Approuvés',  'value' => $stats['approved'], 'color' => 'border-green-200 bg-green-50',   'text' => 'text-green-800'],
        ['label' => 'Rejetés',    'value' => $stats['rejected'], 'color' => 'border-red-200 bg-red-50',       'text' => 'text-red-800'],
    ] as $s)
    <div class="rounded-xl border {{ $s['color'] }} p-4 text-center">
        <p class="text-2xl font-bold {{ $s['text'] }}">{{ $s['value'] }}</p>
        <p class="text-sm font-medium {{ $s['text'] }}">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>

<!-- Filters -->
<div class="flex gap-2 mb-5">
    @foreach(['pending' => 'En attente', 'approved' => 'Approuvés', 'rejected' => 'Rejetés', '' => 'Tous'] as $val => $label)
    <a href="{{ route('admin.kyc.index', ['status' => $val]) }}"
        class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors
        {{ $status === $val ? 'bg-blue-700 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        {{ $label }}
        @if($val === 'pending' && $stats['pending'] > 0)
        <span class="ml-1 bg-yellow-400 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $stats['pending'] }}</span>
        @endif
    </a>
    @endforeach
</div>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Utilisateur</th>
                    <th class="text-left px-5 py-3">Type de document</th>
                    <th class="text-center px-5 py-3">Pièce d'identité</th>
                    <th class="text-center px-5 py-3">Selfie</th>
                    <th class="text-left px-5 py-3">Soumis le</th>
                    <th class="text-center px-5 py-3">Statut</th>
                    <th class="text-center px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($kycDocuments as $kyc)
                @php
                    $statusColors = [
                        'pending'  => 'bg-yellow-100 text-yellow-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                    ];
                    $statusLabels = ['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'];
                    $docExt = $kyc->document_path ? strtolower(pathinfo($kyc->document_path, PATHINFO_EXTENSION)) : null;
                    $docIsImage = in_array($docExt, ['jpg', 'jpeg', 'png', 'webp']);
                    $docUrl = $kyc->document_path ? asset('storage/' . $kyc->document_path) : null;
                    $selfieUrl = $kyc->selfie_path ? asset('storage/' . $kyc->selfie_path) : null;
                @endphp
                <tr class="hover:bg-gray-50" x-data="{ rejectOpen: false }">

                    {{-- Utilisateur --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                 style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                                {{ strtoupper(substr($kyc->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $kyc->user->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $kyc->user->email ?? $kyc->user->phone ?? '—' }}</p>
                                <span class="inline-block mt-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full
                                    {{ in_array($kyc->user->role ?? '', ['owner','both']) ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ ['owner' => 'Propriétaire', 'both' => 'Prop. & Locataire', 'tenant' => 'Locataire', 'admin' => 'Admin'][$kyc->user->role ?? ''] ?? 'Locataire' }}
                                </span>
                            </div>
                        </div>
                    </td>

                    {{-- Type --}}
                    <td class="px-5 py-4">
                        <span class="inline-block bg-gray-100 text-gray-700 text-xs font-semibold px-2 py-1 rounded-lg">
                            {{ $kyc->typeLabel() }}
                        </span>
                        @if($kyc->rejection_reason)
                        <p class="text-xs text-red-500 mt-1 max-w-36">{{ Str::limit($kyc->rejection_reason, 50) }}</p>
                        @endif
                    </td>

                    {{-- Pièce d'identité --}}
                    <td class="px-5 py-4 text-center">
                        @if($docUrl)
                            @if($docIsImage)
                            <div class="flex flex-col items-center gap-1.5">
                                <a href="{{ $docUrl }}" target="_blank">
                                    <img src="{{ $docUrl }}" alt="Document"
                                         class="w-20 h-14 object-cover rounded-lg border border-gray-200 hover:opacity-80 transition cursor-zoom-in">
                                </a>
                                <a href="{{ $docUrl }}" download
                                   class="flex items-center gap-1 text-xs text-blue-600 font-semibold hover:text-blue-800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Télécharger
                                </a>
                            </div>
                            @else
                            <div class="flex flex-col items-center gap-1.5">
                                <div class="w-20 h-14 bg-red-50 rounded-lg border border-red-200 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                                        <path d="M14 2v6h6M9 13h6M9 17h3"/>
                                    </svg>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ $docUrl }}" target="_blank"
                                       class="text-xs text-blue-600 font-semibold hover:text-blue-800">Voir</a>
                                    <a href="{{ $docUrl }}" download
                                       class="text-xs text-blue-600 font-semibold hover:text-blue-800">↓ PDF</a>
                                </div>
                            </div>
                            @endif
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Selfie --}}
                    <td class="px-5 py-4 text-center">
                        @if($selfieUrl)
                        <div class="flex flex-col items-center gap-1.5">
                            <a href="{{ $selfieUrl }}" target="_blank">
                                <img src="{{ $selfieUrl }}" alt="Selfie"
                                     class="w-14 h-14 object-cover rounded-full border-2 border-blue-200 hover:opacity-80 transition cursor-zoom-in">
                            </a>
                            <a href="{{ $selfieUrl }}" download
                               class="flex items-center gap-1 text-xs text-blue-600 font-semibold hover:text-blue-800">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Télécharger
                            </a>
                        </div>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Date --}}
                    <td class="px-5 py-4 text-gray-500 text-xs whitespace-nowrap">
                        {{ $kyc->created_at->format('d/m/Y') }}<br>
                        <span class="text-gray-400">{{ $kyc->created_at->format('H:i') }}</span>
                    </td>

                    {{-- Statut --}}
                    <td class="px-5 py-4 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $statusColors[$kyc->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $statusLabels[$kyc->status] ?? $kyc->status }}
                        </span>
                        @if($kyc->verified_at)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $kyc->verified_at->format('d/m/Y') }}</p>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-4 text-center">
                        @if($kyc->status === 'pending')
                        <div class="flex items-center justify-center gap-2">
                            {{-- Valider --}}
                            <form method="POST" action="{{ route('admin.kyc.verify', $kyc) }}" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit"
                                    onclick="return confirm('Approuver ce document KYC ?')"
                                    class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1.5 rounded-lg text-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Valider
                                </button>
                            </form>

                            {{-- Rejeter --}}
                            <button type="button" @click="rejectOpen = true"
                                class="flex items-center gap-1 bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Rejeter
                            </button>
                        </div>

                        {{-- Rejection modal --}}
                        <div x-show="rejectOpen" x-cloak
                             @click.self="rejectOpen = false"
                             class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md" @click.stop>
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-base font-bold text-gray-900">Motif de rejet</h3>
                                    <button @click="rejectOpen = false" class="text-gray-400 hover:text-gray-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center gap-3 mb-4 p-3 bg-gray-50 rounded-xl">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                         style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                                        {{ strtoupper(substr($kyc->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm text-gray-900">{{ $kyc->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $kyc->typeLabel() }}</p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.kyc.verify', $kyc) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">

                                    <div class="mb-3">
                                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Raison du rejet <span class="text-red-500">*</span></label>
                                        <select name="rejection_reason" required
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-500 mb-2">
                                            <option value="">Sélectionner une raison...</option>
                                            <option value="Document illisible ou flou">Document illisible ou flou</option>
                                            <option value="Document expiré">Document expiré</option>
                                            <option value="Document non conforme">Document non conforme au type demandé</option>
                                            <option value="Selfie non conforme">Selfie non conforme</option>
                                            <option value="Identité non correspondante">Identité non correspondante au document</option>
                                            <option value="Document potentiellement falsifié">Document potentiellement falsifié</option>
                                            <option value="Photo manquante">Photo ou document manquant</option>
                                        </select>
                                        <textarea name="rejection_details" rows="2"
                                            placeholder="Précisions supplémentaires (optionnel)..."
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                                    </div>

                                    <div class="flex gap-3 mt-4">
                                        <button type="button" @click="rejectOpen = false"
                                            class="flex-1 border border-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl text-sm hover:bg-gray-50 transition">
                                            Annuler
                                        </button>
                                        <button type="submit"
                                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                                            Confirmer le rejet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @elseif($kyc->status === 'approved')
                        <span class="text-green-600 text-xs font-semibold">✓ Validé</span>
                        @elseif($kyc->status === 'rejected')
                        <span class="text-red-500 text-xs font-semibold">✕ Rejeté</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-14 text-center">
                        <div class="flex flex-col items-center gap-3 text-gray-400">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <p class="font-medium text-gray-500">Aucun document KYC</p>
                            <p class="text-sm">Aucun document correspondant à ce filtre.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($kycDocuments->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $kycDocuments->links() }}
    </div>
    @endif
</div>
@endsection
