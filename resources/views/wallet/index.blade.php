@extends('layouts.app')
@section('title', 'Mon portefeuille')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">💳 Mon portefeuille</h1>
        <p class="text-gray-500 text-sm mt-0.5">Gérez votre crédit et payez vos réservations instantanément</p>
    </div>

    {{-- Balance card --}}
    <div class="bg-gradient-to-r from-primary-700 to-blue-600 rounded-2xl p-6 mb-6 text-white shadow-lg">
        <p class="text-sm font-medium opacity-80 mb-1">Solde disponible</p>
        <p class="text-4xl font-black tracking-tight">{{ $wallet->formattedBalance() }}</p>
        <div class="flex items-center gap-2 mt-4">
            <span class="text-xs bg-white/20 px-2.5 py-1 rounded-full font-semibold">
                {{ $wallet->currency }}
            </span>
            <span class="text-xs opacity-70">Mis à jour {{ $wallet->updated_at->diffForHumans() }}</span>
        </div>
    </div>

    {{-- Topup form --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6"
         x-data="{ open: false, amount: '' }">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-gray-900">Recharger mon portefeuille</h2>
                <p class="text-sm text-gray-500 mt-0.5">Via Orange Money, Wave, MTN MoMo...</p>
            </div>
            <button @click="open = !open"
                class="bg-primary-700 hover:bg-primary-800 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
                + Recharger
            </button>
        </div>

        <div x-show="open" x-cloak x-transition class="mt-5 border-t border-gray-100 pt-5">
            <form action="{{ route('wallet.topup') }}" method="POST">
                @csrf
                <label class="block text-sm font-semibold text-gray-700 mb-2">Montant à recharger (XOF)</label>
                <div class="flex gap-2 flex-wrap mb-4">
                    @foreach([5000, 10000, 25000, 50000, 100000] as $preset)
                    <button type="button" @click="amount = '{{ $preset }}'"
                        class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold hover:bg-primary-50 hover:border-primary-300 transition-colors"
                        :class="amount == '{{ $preset }}' ? 'bg-primary-100 border-primary-400 text-primary-800' : 'bg-white text-gray-700'">
                        {{ number_format($preset, 0, ',', ' ') }}
                    </button>
                    @endforeach
                </div>
                <div class="flex gap-3">
                    <input type="number" name="amount" x-model="amount"
                        placeholder="Ou saisissez un montant..." min="500" max="5000000"
                        class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-colors">
                        Payer via Mobile Money →
                    </button>
                </div>
                @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </form>
        </div>
    </div>

    {{-- Transactions --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-900">Historique des transactions</h2>
        </div>

        @if($txns->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <p class="text-3xl mb-2">💳</p>
            <p class="text-sm">Aucune transaction pour l'instant</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($txns as $txn)
            @php $info = $txn->typeInfo(); @endphp
            <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0
                    @if($info['color'] === 'green') bg-green-100 text-green-600
                    @elseif($info['color'] === 'blue') bg-blue-100 text-blue-600
                    @elseif($info['color'] === 'red') bg-red-100 text-red-600
                    @else bg-purple-100 text-purple-600 @endif">
                    {{ $info['icon'] }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $txn->description }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $txn->created_at->format('d/m/Y à H:i') }}
                        @if($txn->reference) · <span class="font-mono">{{ $txn->reference }}</span>@endif
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="font-bold text-sm {{ $txn->isCredit() ? 'text-green-600' : 'text-red-600' }}">
                        {{ $txn->isCredit() ? '+' : '-' }}{{ number_format((float)$txn->amount, 0, ',', ' ') }} XOF
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Solde : {{ number_format((float)$txn->balance_after, 0, ',', ' ') }}</p>
                    @if($txn->status === 'pending')
                    <span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded font-semibold">En attente</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @if($txns->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $txns->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection
