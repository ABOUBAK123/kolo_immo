@extends('layouts.app')
@section('title', 'Ouvrir un litige')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <div class="mb-6">
        <a href="{{ route('bookings.show', $booking) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour à la réservation
        </a>
    </div>

    {{-- Warning banner --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div class="text-sm text-amber-800">
            <strong>Avant d'ouvrir un litige</strong>, essayez d'abord de résoudre le problème directement via la messagerie.
            Un litige entraîne le gel du statut de la réservation jusqu'à résolution par notre équipe.
        </div>
    </div>

    {{-- Booking summary --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Réservation concernée</p>
        <div class="flex items-center gap-4">
            @if($booking->property->cover_photo_url)
            <img src="{{ $booking->property->cover_photo_url }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
            @endif
            <div>
                <p class="font-bold text-gray-900">{{ $booking->property->title }}</p>
                <p class="text-sm text-gray-500">{{ $booking->check_in->format('d/m/Y') }} → {{ $booking->check_out->format('d/m/Y') }}</p>
                <p class="text-xs text-gray-400">Réf. {{ $booking->reference }} — {{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('disputes.store', $booking) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
        @csrf

        {{-- Reason --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Motif du litige *</label>
            <div class="space-y-2">
                @foreach($reasons as $value => $label)
                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors
                    {{ old('reason') === $value ? 'border-primary-500 bg-primary-50' : '' }}">
                    <input type="radio" name="reason" value="{{ $value }}"
                        {{ old('reason') === $value ? 'checked' : '' }}
                        class="text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description détaillée *</label>
            <textarea name="description" rows="6" maxlength="3000"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                placeholder="Décrivez précisément le problème rencontré, les dates, les montants en jeu, les tentatives de résolution déjà effectuées...">{{ old('description') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Minimum 50 caractères</p>
            @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Evidence upload --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Preuves (facultatif)</label>
            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-primary-300 transition-colors">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-sm text-gray-500 mb-1">Photos, captures d'écran, documents</p>
                <p class="text-xs text-gray-400 mb-3">JPG, PNG, PDF, Word — max 5 Mo par fichier — max 5 fichiers</p>
                <input type="file" name="evidences[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                    class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>
            @error('evidences.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Submit --}}
        <button type="submit"
            onclick="return confirm('Confirmer l\'ouverture du litige ? La réservation sera gelée en attente de résolution.')"
            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-colors text-sm">
            Ouvrir le litige
        </button>
    </form>
</div>
@endsection
