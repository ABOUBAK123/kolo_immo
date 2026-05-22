@extends('layouts.admin')

@section('title', 'Vérification KYC')

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.kyc.index') }}" class="text-blue-600 hover:underline text-sm">← Documents KYC</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 font-medium">Vérification #{{ $kycDocument->id }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User info -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-bold text-gray-900 mb-4">Utilisateur</h3>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg"
                 style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                {{ strtoupper(substr($kycDocument->user->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-900">{{ $kycDocument->user->name }}</p>
                <p class="text-sm text-gray-500">{{ $kycDocument->user->email ?? $kycDocument->user->phone }}</p>
            </div>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Pays</span><span>{{ $kycDocument->user->country }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Score</span><span>{{ $kycDocument->user->trust_score }}/100</span></div>
            <div class="flex justify-between"><span class="text-gray-500">KYC actuel</span>
                <span class="font-semibold {{ $kycDocument->user->kyc_status === 'verified' ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ $kycDocument->user->kyc_status }}
                </span>
            </div>
        </div>
        <a href="{{ route('admin.users.show', $kycDocument->user) }}"
           class="mt-4 block w-full text-center py-2 px-4 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
            Voir profil complet
        </a>
    </div>

    <!-- Document & action -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Document info -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900">Document soumis</h3>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $kycDocument->status === 'approved' ? 'bg-green-100 text-green-700' : ($kycDocument->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'][$kycDocument->status] ?? $kycDocument->status }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div><span class="text-gray-500 block">Type</span><span class="font-medium">{{ ucfirst(str_replace('_', ' ', $kycDocument->type)) }}</span></div>
                <div><span class="text-gray-500 block">Soumis le</span><span class="font-medium">{{ $kycDocument->created_at->format('d/m/Y H:i') }}</span></div>
                @if($kycDocument->verified_at)
                <div><span class="text-gray-500 block">Vérifié le</span><span class="font-medium">{{ $kycDocument->verified_at->format('d/m/Y H:i') }}</span></div>
                @endif
                @if($kycDocument->rejection_reason)
                <div class="col-span-2"><span class="text-gray-500 block">Motif rejet</span><span class="font-medium text-red-600">{{ $kycDocument->rejection_reason }}</span></div>
                @endif
            </div>
            @if($kycDocument->front_path)
            <div class="mb-3">
                <p class="text-sm text-gray-500 mb-2">Recto :</p>
                <img src="{{ Storage::url($kycDocument->front_path) }}" alt="Document recto"
                     class="max-h-64 rounded-lg border border-gray-200 object-contain">
            </div>
            @endif
            @if($kycDocument->back_path)
            <div>
                <p class="text-sm text-gray-500 mb-2">Verso :</p>
                <img src="{{ Storage::url($kycDocument->back_path) }}" alt="Document verso"
                     class="max-h-64 rounded-lg border border-gray-200 object-contain">
            </div>
            @endif
        </div>

        <!-- Action form -->
        @if($kycDocument->status === 'pending')
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-900 mb-4">Décision</h3>
            <form method="POST" action="{{ route('admin.kyc.verify', $kycDocument) }}" x-data="{ action: '' }">
                @csrf
                <input type="hidden" name="action" :value="action">

                @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
                @endif

                <div x-show="action === 'reject'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif du rejet</label>
                    <textarea name="rejection_reason" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Expliquez pourquoi le document est rejeté..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" @click="action = 'approve'"
                        class="flex-1 py-2 px-4 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                        ✓ Approuver
                    </button>
                    <button type="submit" @click="action = 'reject'"
                        class="flex-1 py-2 px-4 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                        ✕ Rejeter
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
