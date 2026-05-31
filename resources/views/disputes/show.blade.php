@extends('layouts.app')
@section('title', 'Litige #' . $dispute->id)
@section('content')
@php $sym = 'FCFA'; @endphp
<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Litige #{{ $dispute->id }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Ouvert {{ $dispute->created_at->diffForHumans() }}</p>
        </div>
        @php $color = $dispute->statusColor(); @endphp
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold
            bg-{{ $color }}-100 text-{{ $color }}-700">
            <span class="w-2 h-2 rounded-full bg-{{ $color }}-500 inline-block"></span>
            {{ $dispute->statusLabel() }}
        </span>
    </div>

    {{-- Booking card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Réservation</p>
        <div class="flex items-center gap-4">
            @if($dispute->booking->property->cover_photo_url)
            <img src="{{ $dispute->booking->property->cover_photo_url }}" class="w-14 h-14 rounded-xl object-cover">
            @endif
            <div>
                <p class="font-semibold text-gray-900">{{ $dispute->booking->property->title }}</p>
                <p class="text-sm text-gray-500">{{ $dispute->booking->check_in->format('d/m/Y') }} → {{ $dispute->booking->check_out->format('d/m/Y') }}</p>
                <p class="text-xs text-gray-400">{{ $dispute->booking->reference }} — {{ number_format($dispute->booking->total_amount, 0, ',', ' ') }} {{ $sym }}</p>
            </div>
        </div>
    </div>

    {{-- Dispute detail --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4 space-y-4">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Motif</p>
            <p class="font-semibold text-gray-800">{{ $dispute->reasonLabel() }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Description</p>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $dispute->description }}</p>
        </div>
        <div class="flex gap-6 text-sm">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Ouvert par</p>
                <p class="font-medium text-gray-700">{{ $dispute->openedBy->name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Contre</p>
                <p class="font-medium text-gray-700">{{ $dispute->againstUser->name }}</p>
            </div>
        </div>
    </div>

    {{-- Evidences --}}
    @if($dispute->evidences->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Preuves ({{ $dispute->evidences->count() }})</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($dispute->evidences as $ev)
            <a href="{{ $ev->url() }}" target="_blank"
               class="border border-gray-200 rounded-xl p-3 flex items-center gap-2 hover:bg-gray-50 transition-colors">
                @if($ev->isImage())
                <img src="{{ $ev->url() }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                @else
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                @endif
                <span class="text-xs text-gray-600 truncate">{{ $ev->original_name }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Admin resolution --}}
    @if($dispute->isResolved() && $dispute->admin_notes)
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
        <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-2">Décision de l'équipe Kolo Immo</p>
        <p class="text-sm font-semibold text-green-800 mb-1">
            {{ \App\Models\Dispute::RESOLUTIONS[$dispute->resolution] ?? '—' }}
        </p>
        <p class="text-sm text-green-700 whitespace-pre-wrap">{{ $dispute->admin_notes }}</p>
        <p class="text-xs text-green-500 mt-2">Résolu {{ $dispute->resolved_at->format('d/m/Y à H:i') }}</p>
    </div>
    @elseif($dispute->isOpen() || $dispute->isInProgress())
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-sm text-blue-700">
        <strong>En cours d'examen.</strong> Notre équipe traite votre litige sous 48h ouvrées.
        Vous serez notifié par email dès qu'une décision sera rendue.
    </div>
    @endif

</div>
@endsection
