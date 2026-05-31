@extends('layouts.admin')
@section('title', 'Litige #' . $dispute->id)
@section('content')
@php $sym = 'FCFA'; $color = $dispute->statusColor(); @endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <a href="{{ route('admin.disputes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Litiges</a>
        <h1 class="text-xl font-bold text-gray-900 mt-1">Litige #{{ $dispute->id }}</h1>
    </div>
    <span class="px-3 py-1.5 rounded-full text-sm font-bold bg-{{ $color }}-100 text-{{ $color }}-700">
        {{ $dispute->statusLabel() }}
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: detail --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Parties --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-4">Parties impliquées</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-xs font-semibold text-blue-500 uppercase mb-1">Plaignant</p>
                    <p class="font-bold text-gray-900">{{ $dispute->openedBy->name }}</p>
                    <p class="text-sm text-gray-500">{{ $dispute->openedBy->email }}</p>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full mt-1 inline-block">{{ ucfirst($dispute->openedBy->role) }}</span>
                </div>
                <div class="bg-red-50 rounded-xl p-4">
                    <p class="text-xs font-semibold text-red-500 uppercase mb-1">Mis en cause</p>
                    <p class="font-bold text-gray-900">{{ $dispute->againstUser->name }}</p>
                    <p class="text-sm text-gray-500">{{ $dispute->againstUser->email }}</p>
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full mt-1 inline-block">{{ ucfirst($dispute->againstUser->role) }}</span>
                </div>
            </div>
        </div>

        {{-- Booking --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-3">Réservation</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><p class="text-gray-400 text-xs">Référence</p><p class="font-mono font-bold">{{ $dispute->booking->reference }}</p></div>
                <div><p class="text-gray-400 text-xs">Montant</p><p class="font-bold">{{ number_format($dispute->booking->total_amount, 0, ',', ' ') }} {{ $sym }}</p></div>
                <div><p class="text-gray-400 text-xs">Logement</p><p>{{ Str::limit($dispute->booking->property->title, 30) }}</p></div>
                <div><p class="text-gray-400 text-xs">Période</p><p>{{ $dispute->booking->check_in->format('d/m/Y') }} → {{ $dispute->booking->check_out->format('d/m/Y') }}</p></div>
            </div>
        </div>

        {{-- Dispute content --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-3">Détail du litige</h3>
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Motif</p>
            <p class="font-semibold text-gray-800 mb-4">{{ $dispute->reasonLabel() }}</p>
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Description</p>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $dispute->description }}</p>
        </div>

        {{-- Evidences --}}
        @if($dispute->evidences->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-3">Preuves ({{ $dispute->evidences->count() }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($dispute->evidences as $ev)
                <a href="{{ $ev->url() }}" target="_blank"
                   class="border border-gray-200 rounded-xl p-2 hover:bg-gray-50 transition-colors group">
                    @if($ev->isImage())
                    <img src="{{ $ev->url() }}" class="w-full h-20 object-cover rounded-lg mb-1">
                    @else
                    <div class="w-full h-20 bg-gray-100 rounded-lg flex items-center justify-center mb-1">
                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    @endif
                    <p class="text-xs text-gray-500 truncate text-center">{{ $ev->original_name }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Admin notes (existing) --}}
        @if($dispute->admin_notes)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-yellow-700 uppercase mb-1">Notes admin</p>
            <p class="text-sm text-yellow-800 whitespace-pre-wrap">{{ $dispute->admin_notes }}</p>
        </div>
        @endif
    </div>

    {{-- Right: actions --}}
    <div class="space-y-5">

        {{-- Change status --}}
        @if(!$dispute->isResolved())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-3">Mettre à jour le statut</h3>
            <form action="{{ route('admin.disputes.status', $dispute) }}" method="POST" class="space-y-3">
                @csrf @method('PATCH')
                <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($statuses as $val => $s)
                    <option value="{{ $val }}" {{ $dispute->status === $val ? 'selected' : '' }}>{{ $s['label'] }}</option>
                    @endforeach
                </select>
                <textarea name="admin_notes" rows="3" placeholder="Note interne (optionnelle)..."
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ $dispute->admin_notes }}</textarea>
                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg text-sm transition-colors">
                    Mettre à jour
                </button>
            </form>
        </div>

        {{-- Resolve --}}
        <div class="bg-white rounded-xl border border-red-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-1">Résoudre le litige</h3>
            <p class="text-xs text-gray-500 mb-3">Cette action est irréversible.</p>
            <form action="{{ route('admin.disputes.resolve', $dispute) }}" method="POST" class="space-y-3">
                @csrf @method('PATCH')
                <select name="resolution" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-red-400">
                    <option value="">— Choisir une décision —</option>
                    @foreach($resolutions as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                <textarea name="admin_notes" rows="4" placeholder="Décision détaillée (obligatoire, min 20 car.)..."
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                @error('admin_notes')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                @error('resolution')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                <button type="submit"
                    onclick="return confirm('Confirmer la résolution de ce litige ?')"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors">
                    Résoudre
                </button>
            </form>
        </div>
        @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-5">
            <p class="font-bold text-green-800 mb-1">Litige résolu</p>
            <p class="text-sm text-green-700">{{ \App\Models\Dispute::RESOLUTIONS[$dispute->resolution] ?? '—' }}</p>
            <p class="text-xs text-green-500 mt-2">{{ $dispute->resolved_at?->format('d/m/Y à H:i') }}</p>
            @if($dispute->resolvedBy)
            <p class="text-xs text-green-500">par {{ $dispute->resolvedBy->name }}</p>
            @endif
        </div>
        @endif

    </div>
</div>
@endsection
