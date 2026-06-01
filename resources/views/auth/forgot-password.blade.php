@extends('layouts.app')
@section('title', 'Mot de passe oublié')
@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full">

        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Mot de passe oublié</h1>
            <p class="text-gray-500 mt-2 text-sm">Saisissez votre email ou numéro de téléphone.<br>Nous vous enverrons un code de vérification.</p>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <form action="{{ route('password.send-otp') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email ou numéro de téléphone</label>
                    <input type="text" name="contact" value="{{ old('contact') }}" required
                        placeholder="+225 07 00 00 00 00 ou email@exemple.com"
                        class="w-full px-4 py-3 border {{ $errors->has('contact') ? 'border-red-300' : 'border-gray-200' }} rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('contact')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                    class="w-full bg-primary-700 hover:bg-primary-800 text-white font-bold py-3 rounded-xl text-sm transition-colors">
                    Envoyer le code de vérification
                </button>

                <p class="text-center text-sm text-gray-500">
                    <a href="{{ route('login') }}" class="text-primary-700 font-semibold hover:underline">
                        ← Retour à la connexion
                    </a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
