@extends('layouts.admin')

@section('title', 'Litiges')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-900">Litiges ouverts</h1>
    <span class="text-sm text-gray-500">{{ $disputes->total() }} litige(s)</span>
</div>

@if($disputes->isEmpty())
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
    <div class="text-5xl mb-4">✅</div>
    <p class="text-lg font-semibold text-gray-900">Aucun litige ouvert</p>
    <p class="text-gray-500 text-sm mt-1">Tous les litiges ont été résolus.</p>
</div>
@else
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase">
                    <th class="text-left px-5 py-3">Référence</th>
                    <th class="text-left px-5 py-3">Logement</th>
                    <th class="text-left px-5 py-3">Locataire</th>
                    <th class="text-left px-5 py-3">Propriétaire</th>
                    <th class="text-right px-5 py-3">Montant</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-center px-5 py-3">Paiement</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($disputes as $booking)
                @php
                $pc = ['pending' => 'yellow', 'paid' => 'blue', 'escrowed' => 'indigo', 'released' => 'green', 'refunded' => 'gray'][$booking->payment_status] ?? 'gray';
                $pl = ['pending' => 'Non payé', 'paid' => 'Payé', 'escrowed' => 'Séquestre', 'released' => 'Libéré', 'refunded' => 'Remboursé'][$booking->payment_status] ?? $booking->payment_status;
                @endphp
                <tr class="hover:bg-red-50">
                    <td class="px-5 py-3 font-mono text-red-600 text-xs font-bold">{{ $booking->reference }}</td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ Str::limit($booking->property->title ?? '—', 25) }}</p>
                        <p class="text-xs text-gray-400">{{ $booking->property->city ?? '' }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-700">{{ $booking->tenant->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-700">{{ $booking->owner->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-semibold">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $booking->check_in->format('d/m/Y') }} → {{ $booking->check_out->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $pc }}-100 text-{{ $pc }}-800">{{ $pl }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $disputes->links() }}
    </div>
</div>
@endif
@endsection
