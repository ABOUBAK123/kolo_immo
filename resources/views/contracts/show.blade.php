@extends('layouts.app')

@section('title', 'Contrat #' . $contract->reference . ' - Kolo Immo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ otpModal: false, otpCode: '' }">

    <!-- Header -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-blue-700 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Contrat de location</h1>
                        <p class="text-gray-500 text-sm">Référence: <span class="font-mono font-bold text-blue-700">{{ $contract->reference }}</span></p>
                    </div>
                </div>
                <p class="text-gray-400 text-xs">Créé le {{ $contract->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div class="flex items-center gap-3">
                @php
                $statusConfig = [
                    'draft'        => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Brouillon'],
                    'pending'      => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'En attente de signature'],
                    'owner_signed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Signé par le propriétaire'],
                    'fully_signed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Signé par toutes les parties'],
                    'cancelled'    => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Annulé'],
                ];
                $sc = $statusConfig[$contract->status] ?? $statusConfig['pending'];
                @endphp
                <span class="inline-block px-4 py-1.5 rounded-full text-sm font-semibold {{ $sc['bg'] }} {{ $sc['text'] }}">
                    {{ $sc['label'] }}
                </span>
                @if($contract->pdf_path)
                <a href="{{ route('contracts.download', $contract) }}"
                    class="flex items-center gap-2 border border-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Télécharger PDF
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Owner info -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-7 h-7 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600 text-xs font-bold">P</span>
                Propriétaire
            </h2>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nom complet</span>
                    <span class="font-medium text-gray-900">{{ $contract->booking->property->owner->prenom ?? '' }} {{ $contract->booking->property->owner->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Email</span>
                    <span class="font-medium text-gray-900">{{ $contract->booking->property->owner->email ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Téléphone</span>
                    <span class="font-medium text-gray-900">{{ $contract->booking->property->owner->phone ?? '—' }}</span>
                </div>
            </div>
        </div>

        <!-- Tenant info -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-xs font-bold">L</span>
                Locataire
            </h2>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nom complet</span>
                    <span class="font-medium text-gray-900">{{ $contract->booking->tenant->prenom ?? '' }} {{ $contract->booking->tenant->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Email</span>
                    <span class="font-medium text-gray-900">{{ $contract->booking->tenant->email ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Téléphone</span>
                    <span class="font-medium text-gray-900">{{ $contract->booking->tenant->phone ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Property & Rental info -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <h2 class="font-bold text-gray-900 mb-4">Bien loué et conditions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Bien', 'value' => $contract->booking->property->title ?? '—'],
                ['label' => 'Adresse', 'value' => ($contract->booking->property->district ?? '') . ', ' . ($contract->booking->property->city ?? '—')],
                ['label' => 'Arrivée', 'value' => \Carbon\Carbon::parse($contract->booking->check_in)->format('d/m/Y')],
                ['label' => 'Départ', 'value' => \Carbon\Carbon::parse($contract->booking->check_out)->format('d/m/Y')],
                ['label' => 'Durée', 'value' => \Carbon\Carbon::parse($contract->booking->check_in)->diffInDays($contract->booking->check_out) . ' nuit(s)'],
                ['label' => 'Voyageurs', 'value' => $contract->booking->guests . ' personne(s)'],
                ['label' => 'Loyer/nuit', 'value' => number_format($contract->booking->property->price_per_night ?? 0, 0, ',', ' ') . ' FCFA'],
                ['label' => 'Montant total', 'value' => number_format($contract->booking->total_amount ?? 0, 0, ',', ' ') . ' FCFA'],
            ] as $item)
            <div class="p-3 bg-gray-50 rounded-xl">
                <p class="text-xs font-semibold text-gray-500 mb-1">{{ strtoupper($item['label']) }}</p>
                <p class="font-semibold text-gray-900 text-sm">{{ $item['value'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Signature Status -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <h2 class="font-bold text-gray-900 mb-4">Statut des signatures</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Owner signature -->
            <div class="p-4 rounded-xl border {{ $contract->owner_signed_at ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50' }}">
                <div class="flex items-center gap-3 mb-2">
                    @if($contract->owner_signed_at)
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    @else
                    <div class="w-10 h-10 bg-yellow-200 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    @endif
                    <div>
                        <p class="font-bold text-gray-900 text-sm">Propriétaire</p>
                        <p class="text-xs {{ $contract->owner_signed_at ? 'text-green-700' : 'text-yellow-700' }}">
                            {{ $contract->owner_signed_at ? 'Signé le ' . $contract->owner_signed_at->format('d/m/Y à H:i') : 'Signature en attente...' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tenant signature -->
            <div class="p-4 rounded-xl border {{ $contract->tenant_signed_at ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50' }}">
                <div class="flex items-center gap-3 mb-2">
                    @if($contract->tenant_signed_at)
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    @else
                    <div class="w-10 h-10 bg-yellow-200 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    @endif
                    <div>
                        <p class="font-bold text-gray-900 text-sm">Locataire</p>
                        <p class="text-xs {{ $contract->tenant_signed_at ? 'text-green-700' : 'text-yellow-700' }}">
                            {{ $contract->tenant_signed_at ? 'Signé le ' . $contract->tenant_signed_at->format('d/m/Y à H:i') : 'Signature en attente...' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sign button (if current user hasn't signed) -->
        @php
        $userId = Auth::id();
        $isOwner = $userId === ($contract->booking->property->owner_id ?? null);
        $isTenant = $userId === ($contract->booking->tenant_id ?? null);
        $ownerShouldSign = $isOwner && !$contract->owner_signed_at;
        $tenantShouldSign = $isTenant && !$contract->tenant_signed_at;
        @endphp

        @if($ownerShouldSign || $tenantShouldSign)
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <p class="text-sm text-blue-800 font-medium mb-3">
                Votre signature est requise. Un code OTP sera envoyé au numéro {{ Auth::user()->phone }}.
            </p>
            <button @click="otpModal = true; $dispatch('request-otp')"
                class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Signer par OTP
            </button>
        </div>
        @endif
    </div>

    <!-- Contract preview -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-bold text-gray-900 mb-4">Aperçu du contrat</h2>
        <div class="bg-gray-50 rounded-xl p-5 text-sm text-gray-700 leading-relaxed border border-gray-200">
            <p class="font-bold text-center text-gray-900 text-base mb-4">CONTRAT DE LOCATION MEUBLÉE</p>
            <p class="mb-3">Entre les soussignés :</p>
            <p class="mb-2"><strong>Le Propriétaire :</strong> {{ $contract->booking->property->owner->prenom ?? '' }} {{ $contract->booking->property->owner->name ?? '—' }}</p>
            <p class="mb-4"><strong>Le Locataire :</strong> {{ $contract->booking->tenant->prenom ?? '' }} {{ $contract->booking->tenant->name ?? '—' }}</p>
            <p class="mb-3">Il a été convenu ce qui suit :</p>
            <p class="mb-2">Le propriétaire loue au locataire le bien situé à : <strong>{{ $contract->booking->property->address ?? '' }}, {{ $contract->booking->property->city ?? '' }}</strong></p>
            <p class="mb-2">Pour la période du : <strong>{{ \Carbon\Carbon::parse($contract->booking->check_in)->format('d/m/Y') }}</strong> au <strong>{{ \Carbon\Carbon::parse($contract->booking->check_out)->format('d/m/Y') }}</strong></p>
            <p class="mb-2">Loyer : <strong>{{ number_format($contract->booking->property->price_per_night ?? 0, 0, ',', ' ') }} FCFA par nuit</strong></p>
            <p class="mb-4">Montant total : <strong>{{ number_format($contract->booking->total_amount ?? 0, 0, ',', ' ') }} FCFA</strong></p>
            <p class="text-xs text-gray-400">Ce contrat est généré et signé électroniquement via la plateforme Kolo Immo. Il a valeur juridique conformément aux lois en vigueur.</p>
        </div>
    </div>

    <!-- OTP Signing Modal -->
    <div x-show="otpModal" x-cloak @click.self="otpModal = false"
        @request-otp.window="$dispatch('send-otp')"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm" @click.stop x-data="{ otp: '', loading: false, sent: false }" @send-otp.window="sent = true">
            <div class="text-center mb-5">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Signer le contrat</h3>
                <p class="text-sm text-gray-500 mt-1">Un code à 6 chiffres a été envoyé au {{ Auth::user()->phone }}</p>
            </div>

            <form action="{{ route('contracts.sign', $contract) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 text-center">Code OTP</label>
                    <input type="text" name="otp" x-model="otp" maxlength="6" inputmode="numeric"
                        placeholder="••••••"
                        class="w-full text-center text-2xl font-bold tracking-widest px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                        required>
                </div>

                <button type="submit" :disabled="otp.length < 4"
                    class="w-full bg-blue-700 hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 rounded-xl text-sm transition-all mb-3">
                    Confirmer la signature
                </button>
                <button type="button" @click="otpModal = false"
                    class="w-full border border-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
