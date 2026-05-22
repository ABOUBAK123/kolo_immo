@extends('layouts.admin')

@section('title', 'Réservations')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-900">Réservations</h1>
</div>

<!-- Filters -->
<form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Référence</label>
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="KI-..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-36 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">Tous</option>
            @foreach(['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée', 'disputed' => 'Litige'] as $v => $l)
            <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Filtrer</button>
    @if(request()->hasAny(['search', 'status']))
    <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Réinitialiser</a>
    @endif
</form>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase">
                    <th class="text-left px-5 py-3">Référence</th>
                    <th class="text-left px-5 py-3">Logement</th>
                    <th class="text-left px-5 py-3">Locataire</th>
                    <th class="text-left px-5 py-3">Dates</th>
                    <th class="text-right px-5 py-3">Montant</th>
                    <th class="text-center px-5 py-3">Statut</th>
                    <th class="text-center px-5 py-3">Paiement</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($bookings as $booking)
                @php
                $c = ['pending' => 'yellow', 'confirmed' => 'green', 'cancelled' => 'red', 'completed' => 'blue', 'disputed' => 'purple'][$booking->status] ?? 'gray';
                $l = ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée', 'disputed' => 'Litige'][$booking->status] ?? ucfirst($booking->status);
                $pc = ['pending' => 'yellow', 'paid' => 'blue', 'escrowed' => 'indigo', 'released' => 'green', 'refunded' => 'gray'][$booking->payment_status] ?? 'gray';
                $pl = ['pending' => 'Non payé', 'paid' => 'Payé', 'escrowed' => 'Séquestre', 'released' => 'Libéré', 'refunded' => 'Remboursé'][$booking->payment_status] ?? $booking->payment_status;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-blue-600 text-xs font-bold">{{ $booking->reference }}</td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ Str::limit($booking->property->title ?? '—', 25) }}</p>
                        <p class="text-xs text-gray-400">{{ $booking->property->city ?? '' }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-700">{{ $booking->tenant->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-600 text-xs">
                        {{ $booking->check_in->format('d/m/Y') }}<br>{{ $booking->check_out->format('d/m/Y') }}
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $c }}-100 text-{{ $c }}-800">{{ $l }}</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $pc }}-100 text-{{ $pc }}-800">{{ $pl }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">Aucune réservation trouvée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
