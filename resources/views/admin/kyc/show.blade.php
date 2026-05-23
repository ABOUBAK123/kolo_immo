@extends('layouts.admin')

@section('title', 'KYC #' . $kycDocument->id)

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.kyc.index') }}" class="text-blue-600 hover:underline text-sm font-medium">← Documents KYC</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700 font-medium text-sm">{{ $kycDocument->user->name ?? '—' }} — #{{ $kycDocument->id }}</span>
</div>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- User info card --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-bold text-gray-900 mb-4">Utilisateur</h3>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                 style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                {{ strtoupper(substr($kycDocument->user->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-900">{{ $kycDocument->user->name ?? '—' }}</p>
                <p class="text-sm text-gray-500">{{ $kycDocument->user->email ?? $kycDocument->user->phone ?? '—' }}</p>
            </div>
        </div>
        <div class="space-y-2.5 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Rôle</span>
                <span class="font-medium">{{ ['owner' => 'Propriétaire', 'tenant' => 'Locataire', 'both' => 'Prop. & Loc.', 'admin' => 'Admin'][$kycDocument->user->role ?? ''] ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Téléphone</span>
                <span class="font-medium">{{ $kycDocument->user->phone ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Score</span>
                <span class="font-medium">{{ $kycDocument->user->trust_score }}/100</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">KYC actuel</span>
                @php
                $kycColors = ['verified' => 'text-green-600', 'pending' => 'text-yellow-600', 'rejected' => 'text-red-600'];
                $kycLabels = ['verified' => 'Vérifié', 'pending' => 'En attente', 'rejected' => 'Rejeté'];
                @endphp
                <span class="font-semibold {{ $kycColors[$kycDocument->user->kyc_status ?? ''] ?? '' }}">
                    {{ $kycLabels[$kycDocument->user->kyc_status ?? ''] ?? '—' }}
                </span>
            </div>
        </div>
        <a href="{{ route('admin.users.show', $kycDocument->user) }}"
           class="mt-5 block w-full text-center py-2 px-4 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
            Voir profil complet →
        </a>
    </div>

    {{-- Documents + Actions --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Document details --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-gray-900">Document soumis</h3>
                @php
                $statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800'];
                $statusLabels = ['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'];
                @endphp
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statusColors[$kycDocument->status] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $statusLabels[$kycDocument->status] ?? $kycDocument->status }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm mb-6">
                <div>
                    <span class="text-gray-500 block text-xs mb-0.5">Type</span>
                    <span class="font-semibold">{{ $kycDocument->typeLabel() }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs mb-0.5">Soumis le</span>
                    <span class="font-medium">{{ $kycDocument->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                @if($kycDocument->verified_at)
                <div>
                    <span class="text-gray-500 block text-xs mb-0.5">Décision le</span>
                    <span class="font-medium">{{ $kycDocument->verified_at->format('d/m/Y à H:i') }}</span>
                </div>
                @endif
                @if($kycDocument->rejection_reason)
                <div class="col-span-2">
                    <span class="text-gray-500 block text-xs mb-0.5">Motif de rejet</span>
                    <span class="font-medium text-red-600">{{ $kycDocument->rejection_reason }}</span>
                </div>
                @endif
            </div>

            {{-- Document columns --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Pièce d'identité --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Pièce d'identité</p>
                    @if($kycDocument->document_path)
                    @php
                        $ext = strtolower(pathinfo($kycDocument->document_path, PATHINFO_EXTENSION));
                        $isImg = in_array($ext, ['jpg','jpeg','png','webp']);
                        $docUrl = asset('storage/' . $kycDocument->document_path);
                    @endphp
                    @if($isImg)
                    <a href="{{ $docUrl }}" target="_blank">
                        <img src="{{ $docUrl }}" alt="Pièce d'identité"
                             class="w-full max-h-56 object-contain rounded-xl border border-gray-200 hover:opacity-90 transition cursor-zoom-in bg-gray-50">
                    </a>
                    @else
                    <div class="w-full h-40 bg-red-50 rounded-xl border border-red-100 flex flex-col items-center justify-center gap-2">
                        <svg class="w-12 h-12 text-red-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                        </svg>
                        <span class="text-sm font-medium text-red-400">Document PDF</span>
                    </div>
                    @endif
                    <div class="flex gap-3 mt-2">
                        <a href="{{ $docUrl }}" target="_blank"
                           class="flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ouvrir
                        </a>
                        <a href="{{ $docUrl }}" download
                           class="flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-gray-900 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Télécharger
                        </a>
                    </div>
                    @else
                    <div class="w-full h-40 bg-gray-50 rounded-xl border border-dashed border-gray-200 flex items-center justify-center">
                        <p class="text-gray-400 text-sm">Aucun document</p>
                    </div>
                    @endif
                </div>

                {{-- Selfie --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Selfie</p>
                    @if($kycDocument->selfie_path)
                    @php $selfieUrl = asset('storage/' . $kycDocument->selfie_path); @endphp
                    <a href="{{ $selfieUrl }}" target="_blank">
                        <img src="{{ $selfieUrl }}" alt="Selfie"
                             class="w-full max-h-56 object-contain rounded-xl border border-gray-200 hover:opacity-90 transition cursor-zoom-in bg-gray-50">
                    </a>
                    <div class="flex gap-3 mt-2">
                        <a href="{{ $selfieUrl }}" target="_blank"
                           class="flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ouvrir
                        </a>
                        <a href="{{ $selfieUrl }}" download
                           class="flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-gray-900 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Télécharger
                        </a>
                    </div>
                    @else
                    <div class="w-full h-40 bg-gray-50 rounded-xl border border-dashed border-gray-200 flex items-center justify-center">
                        <p class="text-gray-400 text-sm">Aucun selfie fourni</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Decision form --}}
        @if($kycDocument->status === 'pending')
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6" x-data="{ action: '' }">
            <h3 class="font-bold text-gray-900 mb-4">Décision de vérification</h3>

            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('admin.kyc.verify', $kycDocument) }}">
                @csrf
                <input type="hidden" name="action" :value="action">

                <div x-show="action === 'reject'" class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Motif du rejet <span class="text-red-500">*</span></label>
                    <select name="rejection_reason"
                        :required="action === 'reject'"
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
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" @click="action = 'approve'"
                        class="flex-1 flex items-center justify-center gap-2 py-3 px-4 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Valider l'identité
                    </button>
                    <button type="submit" @click="action = 'reject'"
                        class="flex-1 flex items-center justify-center gap-2 py-3 px-4 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Rejeter
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
