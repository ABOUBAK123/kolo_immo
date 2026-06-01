@extends('layouts.admin')
@section('title', 'Distribution des fonds')

@section('content')
<div x-data="{ tab: 'pending', selected: [] }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Distribution des fonds</h1>
            <p class="text-sm text-gray-500 mt-0.5">Libérez les fonds vers les portefeuilles des propriétaires selon la clé de répartition configurée.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700 flex items-start gap-2">
        <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('warning'))
    <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- ── Résumé financier ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php
            $commRate    = config('kolo.platform_commission_percent', 8);
            $sfeeRate    = config('kolo.service_fee_percent', 3);
        @endphp
        @foreach([
            ['label' => 'Réservations en attente',  'value' => $pendingCount,                                                          'color' => 'amber',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Total séquestre (brut)',    'value' => number_format($totalEscrowed, 0, ',', ' ') . ' FCFA',                  'color' => 'blue',   'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z'],
            ['label' => "Commissions plateforme ($commRate%)", 'value' => number_format($totalCommission, 0, ',', ' ') . ' FCFA',      'color' => 'violet', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
            ['label' => 'À verser aux propriétaires','value' => number_format($totalOwnerDue, 0, ',', ' ') . ' FCFA',                 'color' => 'green',  'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as $s)
        <div class="bg-white rounded-xl border border-{{ $s['color'] }}-200 p-4 shadow-sm">
            <div class="w-9 h-9 bg-{{ $s['color'] }}-100 rounded-lg flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-{{ $s['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/>
                </svg>
            </div>
            <p class="text-lg font-bold text-gray-900">{{ $s['value'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Clé de répartition ───────────────────────────────────────────────── --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex flex-wrap gap-6 text-sm">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0"></span>
            <span class="text-blue-800"><strong>Frais de service :</strong> {{ $sfeeRate }}% — payés par le locataire, restent en caisse plateforme</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-violet-500 flex-shrink-0"></span>
            <span class="text-blue-800"><strong>Commission plateforme :</strong> {{ $commRate }}% du loyer — déduite du virement propriétaire</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
            <span class="text-blue-800"><strong>Propriétaire reçoit :</strong> loyer brut – {{ $commRate }}% = <strong>{{ 100 - $commRate }}%</strong> du loyer</span>
        </div>
    </div>

    {{-- ── Tabs ─────────────────────────────────────────────────────────────── --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-5 w-fit">
        <button @click="tab = 'pending'"
            :class="tab === 'pending' ? 'bg-white shadow text-blue-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-2 rounded-lg text-sm transition-all">
            En attente <span class="ml-1 bg-amber-100 text-amber-700 text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $pendingCount }}</span>
        </button>
        <button @click="tab = 'history'"
            :class="tab === 'history' ? 'bg-white shadow text-blue-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-2 rounded-lg text-sm transition-all">
            Historique
        </button>
    </div>

    {{-- ── TAB : EN ATTENTE ─────────────────────────────────────────────────── --}}
    <div x-show="tab === 'pending'">

        {{-- Recherche + Libération en masse --}}
        <form method="GET" class="flex flex-wrap gap-3 items-end mb-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Réservation, propriétaire, logement..."
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                Filtrer
            </button>
        </form>

        <form action="{{ route('admin.payouts.bulk-release') }}" method="POST" id="bulk-form">
            @csrf
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                {{-- Barre d'actions en masse --}}
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="select-all"
                            @change="selected = $event.target.checked ? [...document.querySelectorAll('.row-check')].map(c => c.value) : []"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="select-all" class="text-sm font-medium text-gray-600 cursor-pointer">Tout sélectionner</label>
                        <span x-show="selected.length > 0" class="text-sm text-blue-700 font-semibold">
                            (<span x-text="selected.length"></span> sélectionné(s))
                        </span>
                    </div>
                    <button type="submit" x-show="selected.length > 0"
                        onclick="return confirm('Libérer les fonds pour ' + document.querySelectorAll('.row-check:checked').length + ' réservation(s) ?')"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Libérer les fonds sélectionnés
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase">
                                <th class="px-4 py-3 w-8"></th>
                                <th class="text-left px-4 py-3">Réservation</th>
                                <th class="text-left px-4 py-3">Propriétaire</th>
                                <th class="text-left px-4 py-3">Logement</th>
                                <th class="text-right px-4 py-3">Loyer brut</th>
                                <th class="text-right px-4 py-3 text-violet-600">Commission ({{ $commRate }}%)</th>
                                <th class="text-right px-4 py-3 text-green-600">À verser</th>
                                <th class="text-center px-4 py-3">Période</th>
                                <th class="text-center px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($pending as $booking)
                            @php
                                $ownerAmount = $booking->subtotal - $booking->platform_commission;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="booking_ids[]" value="{{ $booking->id }}"
                                        class="row-check w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        x-model="selected" :value="{{ $booking->id }}"
                                        @change="selected.includes('{{ $booking->id }}') ? selected : selected.push('{{ $booking->id }}')">
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-mono font-bold text-blue-700 text-xs">{{ $booking->reference }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $booking->nights }} nuit(s)</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">{{ $booking->owner->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $booking->owner->email }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-700 font-medium truncate max-w-40">{{ $booking->property->title }}</p>
                                    <p class="text-xs text-gray-400">{{ $booking->property->city }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                    {{ number_format($booking->subtotal, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-violet-700">
                                    − {{ number_format($booking->platform_commission, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-bold text-green-700 text-base">{{ number_format($ownerAmount, 0, ',', ' ') }}</span>
                                    <span class="text-xs text-gray-400 block">FCFA</span>
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-500">
                                    {{ $booking->check_in->format('d/m/Y') }}<br>→ {{ $booking->check_out->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('admin.payouts.release', $booking) }}" method="POST"
                                        onsubmit="return confirm('Virer {{ number_format($ownerAmount, 0) }} FCFA à {{ $booking->owner->name }} ?')">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Virer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center">
                                    <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-gray-400 font-semibold">Aucun fond en attente de libération</p>
                                    <p class="text-gray-400 text-xs mt-1">Tous les virements ont été effectués.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($pending->count() > 0)
                        <tfoot>
                            <tr class="bg-gray-50 font-bold text-sm border-t-2 border-gray-200">
                                <td colspan="4" class="px-4 py-3 text-gray-600">Total page ({{ $pending->count() }} réservations)</td>
                                <td class="px-4 py-3 text-right text-gray-900">{{ number_format($pending->sum('subtotal'), 0, ',', ' ') }} FCFA</td>
                                <td class="px-4 py-3 text-right text-violet-700">− {{ number_format($pending->sum('platform_commission'), 0, ',', ' ') }} FCFA</td>
                                <td class="px-4 py-3 text-right text-green-700">{{ number_format($pending->sum(fn($b) => $b->subtotal - $b->platform_commission), 0, ',', ' ') }} FCFA</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $pending->links() }}
                </div>
            </div>
        </form>
    </div>

    {{-- ── TAB : HISTORIQUE ─────────────────────────────────────────────────── --}}
    <div x-show="tab === 'history'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase">
                            <th class="text-left px-4 py-3">Réservation</th>
                            <th class="text-left px-4 py-3">Propriétaire</th>
                            <th class="text-left px-4 py-3">Logement</th>
                            <th class="text-right px-4 py-3">Loyer brut</th>
                            <th class="text-right px-4 py-3">Commission</th>
                            <th class="text-right px-4 py-3 text-green-600">Viré</th>
                            <th class="text-center px-4 py-3">Date virement</th>
                            <th class="text-center px-4 py-3">Libéré par</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($released as $booking)
                        @php $ownerAmount = $booking->subtotal - $booking->platform_commission; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-mono font-bold text-blue-700 text-xs">{{ $booking->reference }}</p>
                                <p class="text-xs text-gray-400">{{ $booking->nights }} nuit(s)</p>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $booking->owner->name }}</td>
                            <td class="px-4 py-3 text-gray-600 truncate max-w-40">{{ $booking->property->title }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ number_format($booking->subtotal, 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right text-violet-700">− {{ number_format($booking->platform_commission, 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-700">{{ number_format($ownerAmount, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">
                                {{ $booking->funds_released_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-600">
                                {{ $booking->releasedBy->name ?? 'Admin' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">Aucun virement effectué</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
