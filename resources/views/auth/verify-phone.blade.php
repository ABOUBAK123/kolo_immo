@extends('layouts.app')

@section('title', 'Vérification téléphone - Kolo Immo')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full" x-data="{
        countdown: 60,
        canResend: false,
        timer: null,
        startCountdown() {
            this.canResend = false;
            this.countdown = 60;
            this.timer = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(this.timer);
                    this.canResend = true;
                    this.countdown = 0;
                }
            }, 1000);
        }
    }" x-init="startCountdown()">

        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <div class="w-12 h-12 bg-blue-700 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
            </a>

            <!-- SMS icon -->
            <div class="w-20 h-20 mx-auto mt-4 mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900">Vérification du téléphone</h1>
            <p class="text-gray-500 text-sm mt-2 max-w-xs mx-auto">
                Nous avons envoyé un code SMS à :
                <span class="font-semibold text-gray-800 block">{{ session('phone') ?? $phone ?? '+XXX XX XX XX XX' }}</span>
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">

            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('verify.phone') }}" method="POST">
                @csrf
                <input type="hidden" name="phone" value="{{ session('phone') ?? $phone ?? '' }}">

                <!-- OTP Input -->
                <div class="mb-6" x-data="{
                    otp: ['', '', '', '', '', ''],
                    focusNext(index, event) {
                        if (event.target.value && index < 5) {
                            this.$refs['otp' + (index + 1)].focus();
                        }
                    },
                    focusPrev(index, event) {
                        if (event.key === 'Backspace' && !event.target.value && index > 0) {
                            this.$refs['otp' + (index - 1)].focus();
                        }
                    },
                    get fullOtp() { return this.otp.join(''); }
                }">
                    <label class="block text-sm font-semibold text-gray-700 mb-4 text-center">
                        Entrez le code à 6 chiffres
                    </label>
                    <div class="flex items-center justify-center gap-3">
                        @for($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            maxlength="1"
                            x-ref="otp{{ $i }}"
                            x-model="otp[{{ $i }}]"
                            @input="focusNext({{ $i }}, $event)"
                            @keydown="focusPrev({{ $i }}, $event)"
                            @paste.prevent="
                                let paste = $event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                                for(let j = 0; j < paste.length; j++) { otp[j] = paste[j]; }
                                $nextTick(() => { if(paste.length >= 6) $refs.otp5.focus(); else if(paste.length > 0) $refs['otp' + (paste.length - 1)].focus(); });
                            "
                            class="w-12 h-14 text-center text-xl font-bold border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all {{ $errors->has('otp') ? 'border-red-300 bg-red-50' : '' }}"
                            inputmode="numeric" pattern="[0-9]">
                        @endfor
                    </div>
                    <!-- Hidden full OTP input -->
                    <input type="hidden" name="otp" :value="fullOtp">
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md text-sm mb-4">
                    Vérifier mon numéro
                </button>
            </form>

            <!-- Resend -->
            <div class="text-center">
                <p class="text-sm text-gray-500 mb-2">Vous n'avez pas reçu le code ?</p>
                <div x-show="!canResend" class="text-sm text-gray-400">
                    Renvoyer le code dans <span class="font-semibold text-blue-700" x-text="countdown + 's'"></span>
                </div>
                <form x-show="canResend" x-cloak action="{{ route('verify.phone.resend') }}" method="POST">
                    @csrf
                    <input type="hidden" name="phone" value="{{ session('phone') ?? $phone ?? '' }}">
                    <button type="submit" @click="startCountdown()"
                        class="text-blue-700 hover:text-blue-900 font-semibold text-sm underline transition-colors">
                        Renvoyer le code
                    </button>
                </form>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
                    ← Retour à la connexion
                </a>
            </div>
        </div>

        <div class="mt-4 text-center text-xs text-gray-400">
            <p>Problème de réception ? Vérifiez que votre numéro est correct et que vous avez du réseau.</p>
        </div>
    </div>
</div>
@endsection
