@extends('layouts.app')

@section('title', 'Mes commissions - Kolo Immo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mes commissions</h1>
            <p class="text-sm text-gray-500 mt-1">{{ number_format($commissionRate * 100, 0) }}% reversé sur chaque réservation confirmée de vos logements</p>
        </div>
        <a href="{{ route('owner.dashboard') }}"
           class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Tableau de bord
        </a>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-green-200 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Commission ce mois</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($monthlyCommission, 0, ',', ' ') }} <span class="text-sm font-normal">FCFA</span></p>
            <p class="text-xs text-gray-400 mt-1">sur {{ number_format($monthlyRevenue, 0, ',', ' ') }} FCFA de revenus</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-200 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Commission totale</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($totalCommission, 0, ',', ' ') }} <span class="text-sm font-normal">FCFA</span></p>
            <p class="text-xs text-gray-400 mt-1">sur {{ number_format($totalRevenue, 0, ',', ' ') }} FCFA de revenus</p>
        </div>
        <div class="bg-white rounded-2xl border border-purple-200 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Réservations</p>
            <p class="text-2xl font-bold text-purple-600">{{ $rows->count() }}</p>
            <p class="text-xs text-gray-400 mt-1">confirmées / terminées</p>
        </div>
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-5 shadow-sm text-white">
            <p class="text-xs font-medium text-blue-100 uppercase tracking-wide mb-1">Taux de commission</p>
            <p class="text-3xl font-bold">3%</p>
            <p class="text-xs text-blue-200 mt-1">par réservation confirmée</p>
        </div>
    </div>

    {{-- Period filter --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Détail des commissions</h2>
            <div class="flex gap-2">
                @foreach(['all' => 'Tout', 'year' => 'Cette année', 'month' => 'Ce mois'] as $key => $label)
                <a href="{{ route('owner.commissions', ['period' => $key]) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                          {{ $period === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>

        @if($rows->isEmpty())
        <div class="py-16 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-gray-500 font-medium">Aucune commission pour cette période</p>
            <p class="text-sm text-gray-400 mt-1">Les commissions apparaissent dès qu'une réservation est confirmée.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Référence</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Logement</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Locataire</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Période</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Montant</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Commission (3%)</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($rows as $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $row['reference'] }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800 truncate max-w-[160px]">{{ $row['property'] }}</p>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $row['tenant'] }}</td>
                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($row['check_in'])->format('d/m/Y') }}
                            <span class="text-gray-400 mx-1">→</span>
                            {{ \Carbon\Carbon::parse($row['check_out'])->format('d/m/Y') }}
                            <span class="ml-1 text-xs text-gray-400">({{ $row['nights'] }}n)</span>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-800">
                            {{ number_format($row['total'], 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-green-600">{{ number_format($row['commission'], 0, ',', ' ') }} FCFA</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($row['status'] === 'completed')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">Terminée</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700">Confirmée</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-200">
                        <td colspan="4" class="px-6 py-4 font-semibold text-gray-700">Total ({{ $rows->count() }} réservations)</td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900">{{ number_format($totalRevenue, 0, ',', ' ') }} FCFA</td>
                        <td class="px-6 py-4 text-right font-bold text-green-600 text-base">{{ number_format($totalCommission, 0, ',', ' ') }} FCFA</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    {{-- Info note --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
        <p class="font-semibold mb-1">Comment sont calculées les commissions ?</p>
        <p class="text-xs leading-relaxed text-blue-700">
            Une commission de <strong>3%</strong> est prélevée sur le montant total de chaque réservation confirmée ou terminée.
            Les réservations annulées ou en attente ne génèrent pas de commission.
            Le paiement des commissions est effectué selon les conditions contractuelles en vigueur.
        </p>
    </div>

</div>
@endsection
