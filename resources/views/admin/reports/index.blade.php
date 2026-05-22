@extends('layouts.admin')

@section('title', 'Rapports financiers')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-900">Rapports financiers</h1>
    <form method="GET" class="flex gap-2">
        <select name="period" onchange="this.form.submit()"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Ce mois</option>
            <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Cette année</option>
            <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Tout</option>
        </select>
    </form>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'Revenus bruts', 'value' => number_format($totalRevenue, 0, ',', ' ') . ' FCFA', 'color' => 'bg-green-500'],
        ['label' => 'Commission plateforme', 'value' => number_format($platformRevenue, 0, ',', ' ') . ' FCFA', 'color' => 'bg-blue-500'],
        ['label' => 'Transactions', 'value' => $totalTransactions, 'color' => 'bg-purple-500'],
        ['label' => 'Montant moyen', 'value' => number_format($avgTransAmount, 0, ',', ' ') . ' FCFA', 'color' => 'bg-orange-500'],
    ] as $kpi)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="w-9 h-9 {{ $kpi['color'] }} rounded-lg flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ $kpi['value'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $kpi['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Revenue by method -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-900">Revenus par méthode de paiement</div>
        <div class="p-5 space-y-3">
            @forelse($revenueByMethod as $method)
            @php $pct = $totalRevenue > 0 ? round($method->total / $totalRevenue * 100) : 0; @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $method->method) }}</span>
                    <span class="text-gray-500">{{ number_format($method->total, 0, ',', ' ') }} FCFA ({{ $pct }}%)</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 py-6">Aucune transaction</p>
            @endforelse
        </div>
    </div>

    <!-- Daily revenue -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-900">Revenus quotidiens (mois en cours)</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs font-semibold text-gray-400 uppercase">
                        <th class="text-left px-5 py-3">Date</th>
                        <th class="text-right px-5 py-3">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($dailyRevenue as $day)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-700">{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ number_format($day->total, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-6 text-center text-gray-400">Aucune donnée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
