@extends('layouts.app')
@section('title', 'Réinitialisation du mot de passe')
@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full"
        x-data="{
            countdown: 60,
            canResend: false,
            timer: null,
            showPassword: false,
            showConfirm: false,
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
        }"
        x-init="startCountdown()">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Nouveau mot de passe</h1>
            <p class="text-gray-500 mt-2 text-sm">
                Un code a été envoyé au<br>
                <span class="font-semibold text-gray-800">{{ $phone }}</span>
            </p>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

            @if($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3">
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('password.reset') }}" method="POST" class="space-y-5">
                @csrf

                <!-- OTP Input -->
                <div x-data="{
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
                    <label class="block text-sm font-semibold text-gray-700 mb-3 text-center">
                        Code de vérification (6 chiffres)
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
                            class="w-12 h-14 text-center text-xl font-bold border-2 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition-all {{ $errors->has('code') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"
                            inputmode="numeric" pattern="[0-9]">
                        @endfor
                    </div>
                    <input type="hidden" name="code" :value="fullOtp">
                    @error('code')
                    <p class="text-red-500 text-xs mt-2 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nouveau mot de passe -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nouveau mot de passe</label>
                    <div class="relative">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            placeholder="Minimum 8 caractères"
                            class="w-full px-4 py-3 pr-12 border {{ $errors->has('password') ? 'border-red-300' : 'border-gray-200' }} rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-400 hover:text-gray-600">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmer le mot de passe -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmer le mot de passe</label>
                    <div class="relative">
                        <input
                            :type="showConfirm ? 'text' : 'password'"
                            name="password_confirmation"
                            required
                            placeholder="Répétez le mot de passe"
                            class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-400 hover:text-gray-600">
                            <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Exigences du mot de passe -->
                <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
                    <p class="font-semibold text-gray-600 mb-1">Le mot de passe doit contenir :</p>
                    <p>• Au minimum 8 caractères</p>
                    <p>• Au moins une lettre</p>
                    <p>• Au moins un chiffre</p>
                </div>

                <button type="submit"
                    class="w-full bg-primary-700 hover:bg-primary-800 text-white font-bold py-3 rounded-xl text-sm transition-colors">
                    Réinitialiser le mot de passe
                </button>
            </form>

            <!-- Renvoi du code -->
            <div class="mt-5 pt-5 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500 mb-2">Vous n'avez pas reçu le code ?</p>
                <div x-show="!canResend" class="text-sm text-gray-400">
                    Renvoyer dans <span class="font-semibold text-primary-700" x-text="countdown + 's'"></span>
                </div>
                <form x-show="canResend" x-cloak action="{{ route('password.resend') }}" method="POST">
                    @csrf
                    <button type="submit" @click="startCountdown()"
                        class="text-primary-700 hover:text-primary-900 font-semibold text-sm underline transition-colors">
                        Renvoyer le code
                    </button>
                </form>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
                    ← Changer l'email ou le numéro
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
