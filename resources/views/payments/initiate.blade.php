@extends('layouts.app')

@section('title', 'Paiement - Kolo Immo')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{
        paymentMethod: '{{ old('payment_method', 'orange_money') }}',
        phone: '{{ old('payment_phone', Auth::user()->phone ?? '') }}'
    }">

    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Paiement sécurisé</h1>
        <p class="text-gray-500">Votre paiement est protégé par notre système de séquestre</p>
    </div>

    <!-- Booking summary -->
    @if(isset($booking))
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <h2 class="font-bold text-gray-900 mb-4">Récapitulatif de réservation</h2>
        <div class="flex gap-4 items-start">
            <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0">
                @if($booking->property->photos->first())
                <img src="{{ $booking->property->photos->first()->photoUrl() }}" alt="{{ $booking->property->title }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full bg-gradient-to-br from-blue-400 to-indigo-600"></div>
                @endif
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900">{{ $booking->property->title }}</h3>
                <p class="text-gray-500 text-sm">{{ $booking->property->city }}</p>
                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                    <span>{{ \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') }}</span>
                    <span>·</span>
                    <span>{{ $booking->guests }} voyageur(s)</span>
                </div>
                <p class="text-xs font-mono text-blue-700 mt-1">Réf: #{{ $booking->reference }}</p>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex justify-between font-bold text-lg">
                <span>Total à payer</span>
                <span class="text-blue-700">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Payment form -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <h2 class="font-bold text-gray-900 mb-4">Choisir votre mode de paiement</h2>

        <form action="{{ route('payments.process') }}" method="POST">
            @csrf
            @if(isset($booking))
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
            @endif

            <!-- Payment methods -->
            <div class="grid grid-cols-2 gap-3 mb-5">
                @foreach([
                    ['value' => 'orange_money', 'label' => 'Orange Money', 'subtitle' => '#225 07 xx xx xx', 'bg' => 'bg-orange-500', 'border_checked' => 'border-orange-400 bg-orange-50', 'text' => 'text-orange-700'],
                    ['value' => 'wave', 'label' => 'Wave', 'subtitle' => 'Sénégal, CI, Mali', 'bg' => 'bg-blue-500', 'border_checked' => 'border-blue-400 bg-blue-50', 'text' => 'text-blue-700'],
                    ['value' => 'mtn_momo', 'label' => 'MTN Mobile Money', 'subtitle' => 'Ghana, Nigeria', 'bg' => 'bg-yellow-400', 'border_checked' => 'border-yellow-400 bg-yellow-50', 'text' => 'text-yellow-700'],
                    ['value' => 'moov_money', 'label' => 'Moov Money', 'subtitle' => 'CI, Burkina, Bénin', 'bg' => 'bg-red-500', 'border_checked' => 'border-red-400 bg-red-50', 'text' => 'text-red-700'],
                ] as $method)
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="{{ $method['value'] }}"
                        x-model="paymentMethod" class="sr-only peer">
                    <div class="p-4 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300"
                        :class="paymentMethod === '{{ $method['value'] }}' ? '{{ $method['border_checked'] }}' : ''">
                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 {{ $method['bg'] }} rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($method['label'], 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-bold text-sm" :class="paymentMethod === '{{ $method['value'] }}' ? '{{ $method['text'] }}' : 'text-gray-800'">{{ $method['label'] }}</p>
                                <p class="text-gray-400 text-xs">{{ $method['subtitle'] }}</p>
                            </div>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>

            <!-- Phone number -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Numéro Mobile Money
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <input type="tel" name="payment_phone" x-model="phone"
                        placeholder="+225 07 XX XX XX XX"
                        class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('payment_phone') ? 'border-red-300' : '' }}"
                        required>
                </div>
                <p class="text-xs text-gray-400 mt-1">Vous recevrez une demande de paiement à ce numéro. Confirmez sur votre téléphone.</p>
                @error('payment_phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Pay button -->
            <button type="submit"
                class="w-full font-bold py-4 rounded-xl text-base transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-3 text-white"
                :class="paymentMethod === 'orange_money' ? 'bg-orange-500 hover:bg-orange-600' :
                        paymentMethod === 'wave' ? 'bg-blue-500 hover:bg-blue-600' :
                        paymentMethod === 'mtn_momo' ? 'bg-yellow-400 hover:bg-yellow-500' :
                        'bg-red-500 hover:bg-red-600'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Payer {{ isset($booking) ? number_format($booking->total_amount, 0, ',', ' ') . ' FCFA' : 'maintenant' }}
            </button>
        </form>
    </div>

    <!-- Escrow explanation -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-6">
        <div class="flex gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-blue-900 mb-2">Vos fonds sont sécurisés en séquestre</p>
                <p class="text-sm text-blue-700 leading-relaxed">
                    Votre paiement est conservé en sécurité par Kolo Immo jusqu'à votre arrivée confirmée dans le logement.
                    Le propriétaire ne reçoit les fonds qu'après votre check-in. En cas d'annulation valide, vous êtes intégralement remboursé.
                </p>
            </div>
        </div>
    </div>

    <!-- How escrow works -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-bold text-gray-900 mb-4">Comment fonctionne notre séquestre ?</h3>
        <div class="space-y-3">
            @foreach([
                ['step' => '1', 'text' => 'Vous payez via Mobile Money — vos fonds sont sécurisés chez Kolo Immo'],
                ['step' => '2', 'text' => 'Le propriétaire confirme la disponibilité et le logement est réservé pour vous'],
                ['step' => '3', 'text' => 'À votre arrivée, vous confirmez le check-in via l\'application'],
                ['step' => '4', 'text' => 'Les fonds sont libérés au propriétaire seulement après votre confirmation'],
            ] as $s)
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 bg-blue-700 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $s['step'] }}</div>
                <p class="text-sm text-gray-600 mt-0.5">{{ $s['text'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
